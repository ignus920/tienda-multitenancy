<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\WordPress\WordPressService;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Configura en WooCommerce (plugin Tiered Price Table) los precios por escala en %,
 * la cantidad mínima y el múltiplo de compra, leyendo el Excel de módulos LED.
 *
 * Columnas del Excel (encabezados en fila 2, datos desde fila 4):
 *   A = Código Interno (SKU en WordPress)
 *   L = Escala de cantidades (múltiplo / Quantity step)
 *   M = Cant. Mínima
 *   N/O, Q/R, T/U, W/X = Cantidad y % de descuento de las escalas 1 a 4
 */
class ImportWordPressTieredPricing extends Command
{
    protected $signature = 'wp:import-tiered-pricing
        {file : Ruta del Excel (relativa a la raíz del proyecto o absoluta)}
        {--tenant= : ID del tenant con la configuración de WordPress (obligatorio)}
        {--sku= : Procesar un único SKU (Código Interno)}
        {--dry-run : Solo mostrar estado actual y lo que se enviaría, sin modificar nada}';

    protected $description = 'Importa precios por escala (%), cantidad mínima y múltiplo de compra a WooCommerce desde Excel';

    protected const FIRST_DATA_ROW = 4;

    protected const TIER_COLUMNS = [
        ['N', 'O'],
        ['Q', 'R'],
        ['T', 'U'],
        ['W', 'X'],
    ];

    public function handle(): int
    {
        $tenantId  = $this->option('tenant');
        $skuFiltro = $this->option('sku') !== null ? trim($this->option('sku')) : null;
        $dryRun    = (bool) $this->option('dry-run');

        if (!$tenantId) {
            $this->error('Debe indicar --tenant=');
            return self::FAILURE;
        }

        $file = $this->argument('file');
        if (!is_file($file)) {
            $file = base_path($file);
        }
        if (!is_file($file)) {
            $this->error("No se encontró el archivo: {$this->argument('file')}");
            return self::FAILURE;
        }

        $this->info($dryRun ? '🔍 MODO DRY-RUN — no se modificará nada en WordPress' : '✏️  MODO ESCRITURA — se actualizará WordPress');

        // 1. Leer y validar Excel
        [$rows, $invalid] = $this->readExcel($file);
        $this->line('📄 Filas leídas: <info>' . (count($rows) + count($invalid)) . '</info> — válidas: <info>' . count($rows) . '</info> — con errores: <comment>' . count($invalid) . '</comment>');

        if ($skuFiltro !== null) {
            $rows    = array_values(array_filter($rows, fn ($r) => $r['sku'] === $skuFiltro));
            $invalid = array_values(array_filter($invalid, fn ($r) => $r['sku'] === $skuFiltro));

            if (empty($rows) && empty($invalid)) {
                $this->error("El SKU {$skuFiltro} no está en el Excel.");
                return self::FAILURE;
            }
            $this->line("🎯 Solo SKU: <info>{$skuFiltro}</info>");
        }

        foreach ($invalid as $inv) {
            $this->warn("  ⚠️  Fila {$inv['row']} (SKU {$inv['sku']}) omitida: {$inv['error']}");
        }

        if (empty($rows)) {
            $this->warn('No hay filas válidas para procesar.');
            return self::FAILURE;
        }

        // 2. Conexión del tenant
        $tenant = Tenant::where('id', $tenantId)->where('is_active', true)->first();
        if (!$tenant) {
            $this->error("Tenant no encontrado o inactivo: {$tenantId}");
            return self::FAILURE;
        }

        app(TenantManager::class)->setConnection($tenant);
        tenancy()->initialize($tenant);
        $this->line("📌 Tenant: <info>{$tenant->name}</info>");

        $wpService = app(WordPressService::class);
        if (!$wpService->isConfigured()) {
            $this->error('WordPress no está configurado para este tenant.');
            return self::FAILURE;
        }

        if (!$dryRun && $skuFiltro === null) {
            if (!$this->confirm('¿Aplicar precios por escala a ' . count($rows) . ' productos en WordPress?', false)) {
                $this->warn('Cancelado. No se modificó nada.');
                return self::SUCCESS;
            }
        }

        Log::info('🚀 [WP-Tiered] Iniciando importación', [
            'tenant_id' => $tenantId,
            'file'      => $file,
            'sku'       => $skuFiltro ?? 'todos',
            'dry_run'   => $dryRun,
            'filas'     => count($rows),
        ]);

        // 3. Procesar
        $report = [];
        foreach ($invalid as $inv) {
            $report[] = [$inv['sku'], $inv['row'], 'FILA_INVALIDA', $inv['error']];
        }

        foreach ($rows as $row) {
            $this->newLine();
            $this->line("━━━ SKU <info>{$row['sku']}</info> (fila {$row['row']}) ━━━");

            $wpProduct = $wpService->findProductBySku($row['sku']);
            if (!$wpProduct) {
                $this->warn('  ⚠️  No encontrado en WordPress (o no publicado) — omitido');
                $report[] = [$row['sku'], $row['row'], 'NO_ENCONTRADO', ''];
                continue;
            }

            $parentId = $wpProduct['is_variation'] ? $wpProduct['parent_id'] : null;
            $this->line("  Producto: {$wpProduct['name']} (ID {$wpProduct['id']}, tipo {$wpProduct['type']}" . ($parentId ? ", padre {$parentId}" : '') . ')');

            $before = $wpService->getProductById($wpProduct['id'], $parentId);
            if (!$before) {
                $this->error('  ❌ No se pudo consultar el producto — omitido');
                $report[] = [$row['sku'], $row['row'], 'ERROR_CONSULTA', ''];
                continue;
            }

            // Si el plugin no expone sus campos en la API, no escribimos nada
            if (!array_key_exists('tiered_pricing_type', $before)) {
                $this->error('  ❌ La API no devuelve los campos tiered_pricing_* (versión del plugin sin soporte REST). No se modifica.');
                $this->showState('ESTADO ACTUAL', $before);
                $report[] = [$row['sku'], $row['row'], 'SIN_SOPORTE_REST', ''];
                continue;
            }

            $this->showState('ESTADO ACTUAL', $before);
            $this->showPlanned($row, (float) ($before['regular_price'] ?? 0));

            // Texto de venta mínima en la descripción corta (las variaciones no tienen descripción corta)
            $newShort    = null;
            $textAction  = 'SIN_CAMBIO';
            if ($parentId) {
                $textAction = 'VARIACION_SIN_TEXTO';
            } else {
                $raw = $wpService->getProductById($wpProduct['id'], null, 'edit');
                if (!$raw || !array_key_exists('short_description', $raw)) {
                    $this->error('  ❌ No se pudo leer la descripción corta en crudo — omitido');
                    $report[] = [$row['sku'], $row['row'], 'ERROR_CONSULTA', 'short_description'];
                    continue;
                }
                [$newShort, $textAction] = $this->buildShortDescription((string) $raw['short_description'], $row['min']);
            }
            $this->showTextPlan($textAction, $row['min']);

            if ($dryRun) {
                $report[] = [$row['sku'], $row['row'], 'DRY_RUN', $textAction];
                continue;
            }

            $result = $wpService->updateTieredPricing($wpProduct['id'], $row['rules'], $row['min'], $row['step'], $parentId, $newShort);
            if (!$result['success']) {
                $this->error("  ❌ Error HTTP {$result['http_status']}: " . mb_substr((string) $result['body'], 0, 300));
                $report[] = [$row['sku'], $row['row'], 'ERROR_ACTUALIZANDO', 'HTTP ' . $result['http_status']];
                continue;
            }

            $after = $wpService->getProductById($wpProduct['id'], $parentId);
            if ($after) {
                $this->showState('ESTADO DESPUÉS', $after);
            }
            if ($newShort !== null) {
                $afterRaw = $wpService->getProductById($wpProduct['id'], null, 'edit');
                $this->line('    Descripción corta (final): ' . mb_substr(trim((string) ($afterRaw['short_description'] ?? '')), -160));
            }
            $this->info('  ✅ Actualizado');
            $report[] = [$row['sku'], $row['row'], 'ACTUALIZADO', $textAction];
        }

        // 4. Reporte
        $reportPath = storage_path('app/wp_tiered_pricing_' . ($dryRun ? 'dryrun_' : '') . now()->format('Ymd_His') . '.csv');
        $fh = fopen($reportPath, 'w');
        fputcsv($fh, ['sku', 'fila_excel', 'resultado', 'detalle']);
        foreach ($report as $line) {
            fputcsv($fh, $line);
        }
        fclose($fh);

        $counts = array_count_values(array_column($report, 2));
        $this->newLine();
        $this->table(['Resultado', 'Cantidad'], collect($counts)->map(fn ($n, $k) => [$k, $n])->values()->all());
        $this->line("📝 Reporte: <info>{$reportPath}</info>");

        Log::info('✅ [WP-Tiered] Importación finalizada', ['dry_run' => $dryRun, 'resultados' => $counts]);

        return self::SUCCESS;
    }

    /**
     * Lee el Excel y separa filas válidas de filas con errores.
     */
    protected function readExcel(string $file): array
    {
        // Solo datos: con cálculo de fórmulas PhpSpreadsheet se queda colgado con este archivo
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($file)->getActiveSheet();

        $valid   = [];
        $invalid = [];
        $lastRow = $sheet->getHighestDataRow();

        for ($r = self::FIRST_DATA_ROW; $r <= $lastRow; $r++) {
            $sku = trim((string) $this->cell($sheet, 'A', $r));
            if ($sku === '') {
                continue;
            }

            $step  = $this->cell($sheet, 'L', $r);
            $min   = $this->cell($sheet, 'M', $r);
            $error = null;
            $rules = [];

            if (!$this->isPositiveInt($step)) {
                $error = "Escala de cantidades (L) inválida: '{$step}'";
            } elseif (!$this->isPositiveInt($min)) {
                $error = "Cant. Mínima (M) inválida: '{$min}'";
            } elseif (((int) $min) % ((int) $step) !== 0) {
                $error = "La Cant. Mínima ({$min}) no es múltiplo de la escala ({$step})";
            } else {
                $prevQty = 0;
                foreach (self::TIER_COLUMNS as $i => [$qtyCol, $pctCol]) {
                    $qty = $this->cell($sheet, $qtyCol, $r);
                    $pct = $this->cell($sheet, $pctCol, $r);
                    $n   = $i + 1;

                    if (!$this->isPositiveInt($qty) || !is_numeric($pct) || $pct <= 0 || $pct >= 1) {
                        $error = "Escala {$n} incompleta o inválida (cant '{$qty}', % '{$pct}')";
                        break;
                    }
                    if ((int) $qty <= $prevQty) {
                        $error = "Escala {$n} ({$qty}) no es mayor que la anterior ({$prevQty})";
                        break;
                    }
                    if ((int) $qty < (int) $min) {
                        $error = "Escala {$n} ({$qty}) es menor que la Cant. Mínima ({$min})";
                        break;
                    }

                    $prevQty = (int) $qty;
                    // 0.07 → 7 ; 0.125 → 12.5
                    $percent = round((float) $pct * 100, 2);
                    $rules[(int) $qty] = floor($percent) == $percent ? (int) $percent : $percent;
                }
            }

            $data = [
                'row'   => $r,
                'sku'   => $sku,
                'step'  => (int) $step,
                'min'   => (int) $min,
                'rules' => $rules,
            ];

            if ($error) {
                $invalid[] = $data + ['error' => $error];
            } else {
                $valid[] = $data;
            }
        }

        return [$valid, $invalid];
    }

    /**
     * Valor de la celda; si es fórmula (ej. =X3) usa el último valor calculado guardado por Excel.
     */
    protected function cell(Worksheet $sheet, string $col, int $row)
    {
        $cell  = $sheet->getCell($col . $row);
        $value = $cell->getValue();

        if (is_string($value) && str_starts_with($value, '=')) {
            $value = $cell->getOldCalculatedValue();
        }

        return is_string($value) ? trim($value) : $value;
    }

    protected function isPositiveInt($value): bool
    {
        return is_numeric($value) && (float) $value >= 1 && floor((float) $value) == (float) $value;
    }

    /**
     * Calcula la descripción corta con la línea "⚠️ Venta mínima es de N Unidades".
     * Reemplaza el texto viejo ("Este producto se vende en cantidades mínimas de N unidades"),
     * no duplica si ya está y no pone texto cuando el mínimo es 1.
     *
     * @return array [nueva descripción o null si no cambia, acción]
     */
    protected function buildShortDescription(string $raw, int $min): array
    {
        if ($min <= 1) {
            return [null, 'MINIMO_1_SIN_TEXTO'];
        }

        $icon       = '(?:&#x26a0;(?:&#xfe0f;)?|&#9888;(?:&#65039;)?|\x{26A0}\x{FE0F}?)?';
        $newPattern = '/' . $icon . '[ \t]*Venta m(?:í|&iacute;|i)nima es de (\d+) Unidades/iu';
        $oldPattern = '/' . $icon . '[ \t]*Este producto se vende en cantidades m(?:í|&iacute;|i)nimas de \d+ unidades\.?/iu';
        $line       = '&#x26a0;&#xfe0f; Venta mínima es de ' . $min . ' Unidades';

        $hasNew = preg_match($newPattern, $raw, $m) === 1;
        $hasOld = preg_match($oldPattern, $raw) === 1;

        if ($hasNew && (int) $m[1] === $min && !$hasOld) {
            return [null, 'YA_TIENE_TEXTO'];
        }

        if ($hasNew) {
            $desc   = preg_replace($oldPattern, '', $raw);
            $desc   = preg_replace($newPattern, $line, $desc, 1);
            $action = 'ACTUALIZA_TEXTO';
        } elseif ($hasOld) {
            $desc   = preg_replace($oldPattern, $line, $raw, 1);
            $action = 'REEMPLAZA_TEXTO_VIEJO';
        } else {
            $trimmed = rtrim($raw);
            $desc    = $trimmed === '' ? $line : $trimmed . "\n" . $line;
            $action  = 'AGREGA_TEXTO';
        }

        return [$desc, $action];
    }

    protected function showTextPlan(string $action, int $min): void
    {
        $messages = [
            'AGREGA_TEXTO'          => "Se AGREGA al final: ⚠️ Venta mínima es de {$min} Unidades",
            'REEMPLAZA_TEXTO_VIEJO' => "Se REEMPLAZA el texto viejo por: ⚠️ Venta mínima es de {$min} Unidades",
            'ACTUALIZA_TEXTO'       => "Se ACTUALIZA el texto a: ⚠️ Venta mínima es de {$min} Unidades",
            'YA_TIENE_TEXTO'        => 'Ya tiene el texto correcto — no se toca',
            'MINIMO_1_SIN_TEXTO'    => 'Mínimo 1 — no se agrega texto',
            'VARIACION_SIN_TEXTO'   => 'Es variación (sin descripción corta) — no se agrega texto',
        ];
        $this->line('    Descripción corta: ' . ($messages[$action] ?? $action));
    }

    protected function showState(string $title, array $product): void
    {
        $meta = collect($product['meta_data'] ?? [])
            ->filter(fn ($m) => preg_match('/tier|quantit|qty|group_of|step/i', $m['key']))
            ->map(fn ($m) => $m['key'] . ' = ' . json_encode($m['value'], JSON_UNESCAPED_UNICODE))
            ->values()
            ->all();

        $this->line("  <comment>{$title}</comment>");
        $this->line('    Precio normal (regular_price): ' . ($product['regular_price'] ?? '-'));
        foreach ($product as $key => $value) {
            if (str_starts_with($key, 'tiered_pricing_')) {
                $this->line("    {$key}: " . json_encode($value, JSON_UNESCAPED_UNICODE));
            }
        }
        $this->line('    Meta relacionada:');
        foreach ($meta ?: ['(ninguna)'] as $m) {
            $this->line("      - {$m}");
        }
    }

    protected function showPlanned(array $row, float $regularPrice): void
    {
        $this->line('  <comment>SE ENVIARÁ</comment>');
        $this->line("    Cantidad mínima: {$row['min']}  |  Múltiplo (Quantity step): {$row['step']}");

        $tableRows = [[$row['min'] . ' - ' . (array_key_first($row['rules']) - 1), '0%', $regularPrice ? '$' . number_format($regularPrice, 0, ',', '.') : '-']];
        foreach ($row['rules'] as $qty => $pct) {
            $price = $regularPrice ? '$' . number_format($regularPrice * (1 - $pct / 100), 0, ',', '.') : '-';
            $tableRows[] = ["{$qty}+", "{$pct}%", $price];
        }
        $this->table(['Desde (unds)', 'Descuento', 'Precio aprox.'], $tableRows);
    }
}
