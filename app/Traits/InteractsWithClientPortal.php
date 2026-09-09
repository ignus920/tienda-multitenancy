<?php

namespace App\Traits;

use App\Models\Auth\Tenant;
use App\Models\Tenant\CnfModelLog;
use App\Models\Tenant\Customer\VntWarehouse;
use App\Models\Tenant\Invoices\VntInvoices;
use App\Models\Tenant\Invoices\VntInvoicesXsales;
use App\Models\Tenant\Remissions\InvRemissions;
use App\Services\Tenant\TenantManager;

/**
 * Utilidades compartidas por los componentes del Panel del Cliente (Portal B2B).
 *
 * - Establece la conexión del tenant (igual que CustomerPortal).
 * - Resuelve la empresa del usuario del portal (profile_id 18) y CORTA el acceso
 *   a datos de otras empresas.
 * - Expone consultas ya filtradas por la empresa del cliente.
 * - Mapea estados de remisión / factura a etiquetas y colores amigables.
 */
trait InteractsWithClientPortal
{
    /** @var array<int>|null cache de los warehouseIds de la empresa del cliente */
    protected ?array $clientWarehouseIdsCache = null;

    /** @var array<int>|null cache de los invoiceIds de la empresa del cliente */
    protected ?array $clientInvoiceIdsCache = null;

    protected function ensureTenantConnection(): void
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) {
            abort(403, 'Sesión de empresa no válida.');
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            session()->forget('tenant_id');
            abort(403, 'Sesión de empresa no válida.');
        }

        app(TenantManager::class)->setConnection($tenant);
        tenancy()->initialize($tenant);
    }

    /**
     * Empresa (vnt_companies.id) del usuario del portal. Aborta si no corresponde.
     */
    protected function clientCompanyId(): int
    {
        $user = auth()->user();

        if (!$user || (int) $user->profile_id !== 18 || empty($user->tenant_company_id)) {
            abort(403, 'Esta sección es solo para clientes del portal.');
        }

        return (int) $user->tenant_company_id;
    }

    /**
     * warehouseIds de la empresa. vnt_quotes.customerId apunta a uno de estos.
     *
     * @return array<int>
     */
    protected function clientWarehouseIds(): array
    {
        if ($this->clientWarehouseIdsCache === null) {
            $this->clientWarehouseIdsCache = VntWarehouse::where('companyId', $this->clientCompanyId())
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return $this->clientWarehouseIdsCache;
    }

    /**
     * Pedidos (remisiones) de la empresa del cliente.
     */
    protected function clientRemissionQuery()
    {
        $warehouseIds = $this->clientWarehouseIds();

        return InvRemissions::query()
            ->whereHas('quote', fn ($q) => $q->whereIn('customerId', $warehouseIds ?: [0]));
    }

    /**
     * IDs de facturas ligadas a los pedidos de la empresa del cliente.
     * Se resuelve por vnt_invoicesXsales (relación canónica factura↔remisión),
     * lo que cubre también facturas agrupadas.
     *
     * @return array<int>
     */
    protected function clientInvoiceIds(): array
    {
        if ($this->clientInvoiceIdsCache === null) {
            $remissionIds = $this->clientRemissionQuery()->pluck('id')->all();

            $viaXsales = VntInvoicesXsales::whereIn('remissionId', $remissionIds ?: [0])
                ->pluck('invoiceId');

            // Respaldo: facturas cuyo quote pertenece a la empresa (POS sin xsales)
            $viaQuote = VntInvoices::whereHas('quote', fn ($q) => $q->whereIn('customerId', $this->clientWarehouseIds() ?: [0]))
                ->pluck('id');

            $this->clientInvoiceIdsCache = $viaXsales->merge($viaQuote)
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        return $this->clientInvoiceIdsCache;
    }

    /**
     * Facturas de la empresa del cliente.
     */
    protected function clientInvoiceQuery()
    {
        return VntInvoices::query()->whereIn('id', $this->clientInvoiceIds() ?: [0]);
    }

    protected function findClientRemissionOrFail(int $remissionId): InvRemissions
    {
        $remission = $this->clientRemissionQuery()->whereKey($remissionId)->first();
        abort_if(!$remission, 403, 'Este pedido no pertenece a tu empresa.');

        return $remission;
    }

    protected function findClientInvoiceOrFail(int $invoiceId): VntInvoices
    {
        abort_unless(in_array($invoiceId, $this->clientInvoiceIds(), true), 403, 'Esta factura no pertenece a tu empresa.');
        $invoice = VntInvoices::find($invoiceId);
        abort_if(!$invoice, 404);

        return $invoice;
    }

    // ---------------------------------------------------------------------
    // Etiquetas de estado
    // ---------------------------------------------------------------------

    /**
     * Pasos ordenados del seguimiento de un pedido (línea de tiempo).
     * Se mapean varios nombres de estado por-tenant al mismo paso.
     *
     * @return array<int, array{key:string, label:string, matches:array<string>}>
     */
    public function orderTimelineSteps(): array
    {
        return [
            ['key' => 'registrado',  'label' => 'Pedido recibido',     'matches' => ['REGISTRADO']],
            ['key' => 'alistamiento','label' => 'En alistamiento',      'matches' => ['ALISTAMIENTO']],
            ['key' => 'empacado',    'label' => 'Empacado',             'matches' => ['EMPACADO']],
            ['key' => 'ruta',        'label' => 'En camino',            'matches' => ['EN RECORRIDO', 'ENTREGADO A RUTA', 'EN RUTA']],
            ['key' => 'entregado',   'label' => 'Entregado',            'matches' => ['ENTREGADO']],
        ];
    }

    /**
     * Etiqueta + color (tailwind) para el badge de estado de un pedido.
     *
     * @return array{label:string, color:string}
     */
    public function orderStatusBadge(?string $status): array
    {
        return match (strtoupper((string) $status)) {
            'REGISTRADO'                        => ['label' => 'Recibido',       'fp' => 'blue'],
            'ALISTAMIENTO'                      => ['label' => 'En alistamiento', 'fp' => 'amber'],
            'EMPACADO'                          => ['label' => 'Empacado',       'fp' => 'blue'],
            'EN RECORRIDO', 'ENTREGADO A RUTA', 'EN RUTA' => ['label' => 'En camino', 'fp' => 'cyan'],
            'ENTREGADO'                         => ['label' => 'Entregado',      'fp' => 'green'],
            'DEVUELTO'                          => ['label' => 'Devuelto',       'fp' => 'amber'],
            'ANULADO'                           => ['label' => 'Anulado',        'fp' => 'red'],
            'VENCIDO'                           => ['label' => 'Vencido',        'fp' => 'gray'],
            default                            => ['label' => ucfirst(strtolower((string) $status ?: 'Sin estado')), 'fp' => 'gray'],
        };
    }

    /**
     * Etiqueta + color para el estado de pago de una factura.
     *
     * @return array{label:string, color:string}
     */
    public function invoicePaymentBadge(?string $statusPayment): array
    {
        return match (strtoupper((string) $statusPayment)) {
            'PAGADO'  => ['label' => 'Pagada',    'fp' => 'green'],
            'ABONO'   => ['label' => 'Con abono', 'fp' => 'amber'],
            'ANULADO' => ['label' => 'Anulada',   'fp' => 'red'],
            default   => ['label' => 'Pendiente', 'fp' => 'amber'],
        };
    }

    /**
     * Reconstruye la línea de tiempo de un pedido a partir de cnf_model_logs.
     * Nunca falla si faltan registros: los pasos sin fecha se marcan como
     * "completado" si el pedido ya pasó ese punto, o "pendiente" si no.
     *
     * @return array<int, array{key:string, label:string, date:?string, state:string}>
     *         state ∈ done | current | pending
     */
    public function buildOrderTimeline(InvRemissions $remission): array
    {
        $steps = $this->orderTimelineSteps();

        // Fechas de cada transición de estado desde la bitácora
        $statusDates = [];
        try {
            $logs = CnfModelLog::query()
                ->where('model_type', 'like', '%InvRemissions%')
                ->where('model_id', $remission->id)
                ->where('action', 'update')
                ->orderBy('created_at')
                ->get(['new_values', 'created_at']);

            foreach ($logs as $log) {
                $newStatus = strtoupper((string) ($log->new_values['status'] ?? ''));
                if ($newStatus !== '' && !isset($statusDates[$newStatus])) {
                    $statusDates[$newStatus] = (string) $log->created_at;
                }
            }
        } catch (\Throwable $e) {
            // bitácora no disponible: seguimos sin fechas
        }

        // El primer paso siempre tiene fecha: la creación de la remisión
        if (!isset($statusDates['REGISTRADO'])) {
            $statusDates['REGISTRADO'] = (string) $remission->created_at;
        }

        $currentStatus = strtoupper((string) $remission->status);
        $isTerminal = in_array($currentStatus, ['ANULADO', 'DEVUELTO', 'VENCIDO'], true);

        // Índice del paso alcanzado según el estado actual
        $reachedIndex = -1;
        foreach ($steps as $i => $step) {
            if (in_array($currentStatus, $step['matches'], true)) {
                $reachedIndex = $i;
            }
        }
        // Si el estado actual no es un paso del timeline (terminal), el avance
        // se infiere de las fechas registradas.
        if ($reachedIndex === -1) {
            foreach ($steps as $i => $step) {
                foreach ($step['matches'] as $m) {
                    if (isset($statusDates[$m])) {
                        $reachedIndex = max($reachedIndex, $i);
                    }
                }
            }
        }

        $timeline = [];
        foreach ($steps as $i => $step) {
            $date = null;
            foreach ($step['matches'] as $m) {
                if (isset($statusDates[$m])) {
                    $date = $statusDates[$m];
                    break;
                }
            }

            if ($i < $reachedIndex) {
                $state = 'done';
            } elseif ($i === $reachedIndex) {
                $state = $isTerminal ? 'done' : 'current';
            } else {
                $state = 'pending';
            }

            $timeline[] = [
                'key'   => $step['key'],
                'label' => $step['label'],
                'date'  => $date,
                'state' => $state,
            ];
        }

        return $timeline;
    }
}
