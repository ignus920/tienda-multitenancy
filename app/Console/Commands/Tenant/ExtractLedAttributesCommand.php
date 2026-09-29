<?php

namespace App\Console\Commands\Tenant;

use Illuminate\Console\Command;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvItemsDimensions;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;

class ExtractLedAttributesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:extract-led-attributes {tenant_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extrae Lm/m y CC/CV de las descripciones y actualiza inv_items_dimensions (Multitenant)';

    /**
     * Execute the console command.
     */
    public function handle(TenantManager $tenantManager)
    {
        $tenantId = $this->argument('tenant_id');

        if (!$tenantId) {
            $tenantId = $this->ask('Por favor, ingresa el ID del tenant (ej. 132 para produccion, 131 para local)');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $this->error("Tenant con ID {$tenantId} no encontrado.");
            return;
        }

        // Conectar a la base de datos del Tenant
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);

        $this->info("Conectado exitosamente al Tenant: {$tenant->db_name}");

        // Traemos todos los productos
        $items = Items::all();
        $updatedCount = 0;
        $createdDimensionsCount = 0;

        $this->output->progressStart($items->count());

        foreach ($items as $item) {
            // Unimos nombre y descripción en mayúsculas para facilitar la búsqueda
            $desc = strtoupper($item->description . ' ' . $item->name);
            $lm_per_meter = null;
            $electrical_type = null;
            $voltage = null;
            $power = null;
            $width = null;
            $quntityxbox = null;

            // 1. Extraer Lm/m (Lúmenes por metro)
            if (preg_match('/(\d+(?:\.\d+)?)\s*(?:LM\/M|LM\/MT|L\/M|LUMEN\/M|LUMENES\/M|LUMENES\/MT)/', $desc, $matches)) {
                $lm_per_meter = $matches[1];
            }

            // 2. Extraer Tipo Eléctrico (CC o CV)
            if (strpos($desc, 'CORRIENTE CONSTANTE') !== false) {
                $electrical_type = 'CC';
            } elseif (strpos($desc, 'VOLTAJE CONSTANTE') !== false) {
                $electrical_type = 'CV';
            }

            // 3. Extraer Voltaje (ej. 12V, 24V, 110V)
            if (preg_match('/(\d+(?:\.\d+)?)\s*V\b/', $desc, $matches)) {
                $voltage = $matches[1];
            }

            // 4. Extraer Potencia (ej. 10W, 13.68W, 7.5W)
            if (preg_match('/(\d+(?:\.\d+)?)\s*W\b/', $desc, $matches)) {
                $power = $matches[1];
            }

            // 5. Extraer Ancho (ej. 10MM, 8MM)
            if (preg_match('/(\d+(?:\.\d+)?)\s*MM\b/', $desc, $matches)) {
                $width = $matches[1];
            }

            // 6. Extraer Cantidad por Caja (ej. CJ100, CJ 50, CJ-200)
            if (preg_match('/CJ\s*-?\s*(\d+)/', $desc, $matches)) {
                $quntityxbox = $matches[1];
            }

            // Si encontró al menos un valor de cualquiera de los campos
            if ($lm_per_meter || $electrical_type || $voltage !== null || $power !== null || $width !== null || $quntityxbox !== null) {
                $dimension = InvItemsDimensions::where('item_id', $item->id)->first();
                $isNew = false;
                
                if (!$dimension) {
                    $dimension = new InvItemsDimensions();
                    $dimension->item_id = $item->id;
                    $dimension->high = 0;
                    $dimension->long = 0;
                    $dimension->width = 0;
                    $dimension->voltage = 0;
                    $dimension->power = 0;
                    $dimension->weight = 0;
                    $dimension->quntityxbox = 0;
                    $isNew = true;
                    $createdDimensionsCount++;
                }

                $updated = false;

                // Solo actualizamos si el campo está vacío para no sobreescribir
                if ($lm_per_meter && empty($dimension->lumens_per_meter)) {
                    $dimension->lumens_per_meter = $lm_per_meter;
                    $updated = true;
                }
                if ($electrical_type && empty($dimension->electrical_type)) {
                    $dimension->electrical_type = $electrical_type;
                    $updated = true;
                }
                if ($voltage !== null && empty($dimension->voltage) && empty($dimension->voltage)) { // Si voltage == 0 (empty)
                    $dimension->voltage = $voltage;
                    $updated = true;
                }
                if ($power !== null && empty($dimension->power)) {
                    $dimension->power = $power;
                    $updated = true;
                }
                if ($width !== null && empty($dimension->width)) {
                    $dimension->width = $width;
                    $updated = true;
                }
                if ($quntityxbox !== null && empty($dimension->quntityxbox)) {
                    $dimension->quntityxbox = $quntityxbox;
                    $updated = true;
                }

                if ($updated || $isNew) {
                    $dimension->save();
                    $updatedCount++;
                }
            }
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        
        $this->info("========================================");
        $this->info("PROCESO FINALIZADO CON ÉXITO");
        $this->info("Nuevos registros creados en dimensiones: {$createdDimensionsCount}");
        $this->info("Total de Items que recibieron data extraída: {$updatedCount}");
        $this->info("========================================");
    }
}
