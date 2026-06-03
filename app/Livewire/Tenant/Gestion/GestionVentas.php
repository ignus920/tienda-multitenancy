<?php

namespace App\Livewire\Tenant\Gestion;

use Livewire\Component;
use App\Traits\HasCompanyConfiguration;
use App\Models\Tenant\Quoter\VntQuote;
use App\Models\Tenant\Sales\VntQuoteFollowup;
use App\Models\Tenant\Sales\VntFollowupStatus;
use App\Models\Auth\User;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GestionVentas extends Component
{
    use HasCompanyConfiguration;

    // Filtros
    public $fechaIni;
    public $fechaFin;
    public $advisorId = 0;
    public $filterStatus = 0; // 0: Todo, 1: Sin ventas, 2: Con pendientes

    // Selección
    public $selectedClientId = null;
    public $selectedClientName = "";
    public $selectedQuoteId = null;
    public $quoteItems = [];

    // Formulario de Seguimiento
    public $comment = "";
    public $followupStatusId = "";

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    public function mount()
    {
        $this->fechaIni = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function selectClient($clientId, $clientName)
    {
        $this->selectedClientId = $clientId;
        $this->selectedClientName = $clientName;
    }

    public function openFollowupModal($quoteId)
    {
        $this->ensureTenantConnection();
        $this->selectedQuoteId = $quoteId;
        $this->reset('comment', 'followupStatusId');
        
        // Cargar items de la cotización
        $quote = VntQuote::with('detalles')->find($quoteId);
        $this->quoteItems = $quote ? $quote->detalles->toArray() : [];

        $this->dispatch('open-modal', 'modalGestion');
    }

    public function openHistoryModal($quoteId)
    {
        $this->selectedQuoteId = $quoteId;
        $this->dispatch('open-modal', 'modalHistorial');
    }

    public function saveFollowup()
    {
        $this->validate([
            'comment' => 'required|max:500',
            'followupStatusId' => 'required|exists:tenant.vnt_followup_statuses,id',
        ]);

        VntQuoteFollowup::create([
            'quote_id' => $this->selectedQuoteId,
            'user_id' => auth()->id(),
            'status_id' => $this->followupStatusId,
            'comment' => $this->comment,
        ]);

        $this->dispatch('close-modal', 'modalGestion');
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Seguimiento registrado correctamente']);
    }

    public function getAdvisorsProperty()
    {
        $tenantId = session('tenant_id');
        
        if (!$tenantId) return collect();

        // Solo usuarios vinculados a este tenant
        return User::whereHas('tenants', function($query) use ($tenantId) {
            $query->where('tenants.id', $tenantId);
        })->get();
    }

    public function getFollowupStatusesProperty()
    {
        return VntFollowupStatus::all();
    }

    public function getClientsSummaryProperty()
    {
        $selectedWarehouseId = session('selectedWarehouseId');

        $query = DB::connection('tenant')->table('vnt_quotes as q')
            ->join('vnt_contacts as c', 'c.warehouseId', '=', 'q.customerId')
            ->select(
                'c.warehouseId as client_id',
                DB::raw("CONCAT_WS(' ', c.firstName, c.secondName, c.lastName, c.secondLastName) as client_name"),
                'q.userId',
                DB::raw("COUNT(q.id) as total_quotes"),
                DB::raw("SUM(CASE WHEN q.status IN ('FACTURADO', 'REMISIN') THEN 1 ELSE 0 END) as sold_quotes")
            )
            ->whereBetween(DB::raw('DATE(q.created_at)'), [$this->fechaIni, $this->fechaFin])
            ->groupBy('c.warehouseId', 'c.firstName', 'c.secondName', 'c.lastName', 'c.secondLastName', 'q.userId');

        // Filtrar por la bodega seleccionada en la sesión
        if ($selectedWarehouseId) {
            $query->where('q.warehouseId', $selectedWarehouseId);
        }

        if ($this->advisorId > 0) {
            $query->where('q.userId', $this->advisorId);
        }

        $results = $query->get();

        // Aplicar filtros de estado en PHP para mayor claridad
        return $results->filter(function ($item) {
            $item->pending_quotes = $item->total_quotes - $item->sold_quotes;
            
            if ($this->filterStatus == 1) { // Sin ventas
                return $item->sold_quotes == 0;
            }
            if ($this->filterStatus == 2) { // Con pendientes
                return $item->pending_quotes > 0;
            }
            return true;
        });
    }

    public function getSelectedClientQuotesProperty()
    {
        if (!$this->selectedClientId) return collect();

        $selectedWarehouseId = session('selectedWarehouseId');

        $query = VntQuote::where('customerId', $this->selectedClientId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$this->fechaIni, $this->fechaFin]);

        if ($selectedWarehouseId) {
            $query->where('warehouseId', $selectedWarehouseId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getQuoteHistory($quoteId)
    {
        return VntQuoteFollowup::where('quote_id', $quoteId)
            ->with(['status', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.tenant.gestion.gestion-ventas', [
            'clients' => $this->clients_summary,
            'advisors' => $this->advisors,
            'quotes' => $this->selected_client_quotes,
            'statuses' => $this->followup_statuses
        ])->layout('layouts.app');
    }

    /**
     * Prepara y abre la ventana de impresión para una cotización.
     */
    public function printQuote($id)
    {
        $this->ensureTenantConnection();
        $this->initializeCompanyConfiguration();

        try {
            $quote = VntQuote::with(['customer', 'detalles'])->find($id);

            if (!$quote) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Cotización no encontrada']);
                return;
            }

            // Obtener información de la empresa
            $company = $this->getCompanyInfo($quote);

            $tableName = ($quote->status === 'REMISIÓN') ? 'inv_detail_remissions' : 'vnt_detail_quotes';
            $tableNameId = ($quote->status === 'REMISIÓN') ? 'remissionId' : 'quoteId';
            
            // Calcular el peso total de los items
            $totalWeight = DB::connection('tenant')->table($tableName)
                ->join('inv_items_dimensions', $tableName . '.itemId', '=', 'inv_items_dimensions.item_id')
                ->where($tableName . '.' . $tableNameId, $id)
                ->sum(DB::raw('inv_items_dimensions.weight * ' . $tableName . '.quantity'));

            $observations = DB::connection('tenant')->table('inv_remissions')
                ->where('quoteId', $id)
                ->select('observations_delivery', 'obs')->first();

            // Determinar el formato de impresión según configuración
            $printFormat = $this->getOptionValue(3) ?? 1; // 0 = POS Simple, 1 = Institucional

            // Determinar el título del documento
            $documentTitle = ($quote->status === 'REMISIÓN') ? 'REMISIÓN' : 'COTIZACIÓN';

            // Datos para la vista
            $data = [
                'quote' => $quote,
                'customer' => $quote->customer,
                'company' => $company,
                'documentTitle' => $documentTitle,
                'showQR' => true,
                'defaultObservations' => 'Observaciones por defecto',
                'totalWeight' => $totalWeight,
                'observations_delivery' => $observations->observations_delivery ?? null,
                'obs' => $observations->obs ?? null,
            ];

            // Seleccionar la vista según el formato
            $viewName = ($printFormat === 1)
                ? 'livewire.tenant.quoter.print.print-carta'
                : 'livewire.tenant.quoter.print.print-pos';

            $html = view($viewName, $data)->render();

            // Guardar temporalmente el HTML
            $tempFileName = 'quote_' . $id . '_' . time() . '.html';
            $tempPath = storage_path('app/temp/' . $tempFileName);

            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            file_put_contents($tempPath, $html);

            $printUrl = route('quoter.print.temp', ['file' => $tempFileName]);

            $this->dispatch('open-print-window', [
                'url' => $printUrl,
                'format' => $printFormat === 1 ? 'carta' : 'pos'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en printQuote: ' . $e->getMessage());
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error al preparar la impresión']);
        }
    }

    /**
     * Obtiene la información de la empresa vinculada al usuario.
     */
    private function getCompanyInfo($quote = null)
    {
        try {
            $userId = auth()->id();
            return DB::connection('central')->table('users as u')
                ->join('user_tenants as uXt', 'uXt.user_id', '=', 'u.id')
                ->join('tenants as t', 't.id', '=', 'uXt.tenant_id')
                ->join('vnt_companies as v', 'v.id', '=', 't.company_id')
                ->join('vnt_warehouses as w', 'w.companyId', '=', 'v.id')
                ->join('cities as c', 'c.id', '=', 'w.cityId')
                ->join('cnf_type_identifications as ti', 'ti.id', '=', 'v.typeIdentificationId')
                ->where('u.id', $userId)
                ->where('w.main', 1)
                ->select([
                    'v.businessName',
                    'w.address as billingAddress',
                    'c.name as city',
                    'ti.acronym',
                    'v.identification',
                    'v.checkDigit',
                    'v.phone',
                    'v.email',
                    'v.webPage',
                    'v.logo'
                ])->first();
        } catch (\Exception $e) {
            Log::error('Error en getCompanyInfo: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Asegura que la conexión al tenant esté activa.
     */
    private function ensureTenantConnection(): void
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return;
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return;
        }

        // Establecer conexión tenant vía TenantManager
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}
