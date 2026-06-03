<?php

namespace App\Livewire\Tenant\Cartera;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Tenant\Remissions\InvRemissions;
use App\Models\Tenant\Sales\VntOrderAuthorization;
use App\Models\Tenant\Quoter\VntQuote;
use App\Traits\HasCompanyConfiguration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CarteraList extends Component
{
    use WithPagination, HasCompanyConfiguration, WithFileUploads;

    // Propiedades para soporte de pago
    public $showUploadModal = false;
    public $proofPaymentFile;

    // Filtros
    public $fromDate;
    public $toDate;
    public $search = '';
    public $statusPacking = '';
    public $statusDispatch = '';
    public $statusPayment = '';
    public $activeFilter = ''; // empaque, despacho, pago, anulados

    public $perPage = 10;
    public $searchNit = '';
    public $searchName = '';
    public $searchQuote = '';
    public $showAdvancedSearch = false;
    public $showJustificacionModal = false;
    public $justificacionText = '';
    public $selectedRemissionId = null;
    public $isDesconfirmarPago = false;

    protected $queryString = [
        'fromDate' => ['except' => ''],
        'toDate' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->fromDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $this->toDate = Carbon::now()->addDays(7)->format('Y-m-d');
    }

    /**
     * Alterna la autorización de un pedido con lógica de cascada
     */
    public function toggleAuthorization($remissionId, $type)
    {
        $this->ensureTenantConnection();
        try {
            DB::connection('tenant')->beginTransaction();
            
            // Validar si el pedido está anulado
            $remission = InvRemissions::find($remissionId);
            if ($remission && $remission->status === 'ANULADO') {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'No se pueden modificar autorizaciones de un pedido anulado.'
                ]);
                DB::connection('tenant')->rollBack();
                return;
            }

            // Obtener el estado actual (si existe)
            $lastAuth = VntOrderAuthorization::where('remission_id', $remissionId)
                ->where('auth_type', $type)
                ->latest()
                ->first();

            $newStatus = $lastAuth ? !$lastAuth->status : true;

            // Lógica de Cascada
            if ($newStatus) {
                // Si autoriza PAGO -> Autoriza DESPACHO y EMPAQUE
                if ($type === 'pago') {
                    $this->saveAuth($remissionId, 'pago', true);
                    $this->saveAuth($remissionId, 'despacho', true);
                    $this->saveAuth($remissionId, 'empaque', true);
                } 
                // Si autoriza DESPACHO -> Autoriza EMPAQUE
                elseif ($type === 'despacho') {
                    $this->saveAuth($remissionId, 'despacho', true);
                    $this->saveAuth($remissionId, 'empaque', true);
                } 
                else {
                    $this->saveAuth($remissionId, $type, true);
                }
            } else {
                // REQUERIMIENTO: Si desautoriza PAGO, pedir justificación
                if ($type === 'pago') {
                    $this->selectedRemissionId = $remissionId;
                    $this->isDesconfirmarPago = true;
                    $this->justificacionText = '';
                    $this->showJustificacionModal = true;
                    DB::connection('tenant')->rollBack(); // No guardar nada aún
                    return;
                }

                // Si desautoriza otros niveles, solo desautoriza ese nivel
                $this->saveAuth($remissionId, $type, false);
            }

            DB::connection('tenant')->commit();
            
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Autorización actualizada correctamente.'
            ]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            \Illuminate\Support\Facades\Log::error('❌ Error en toggleAuthorization: ' . $e->getMessage(), [
                'exception' => $e,
                'remissionId' => $remissionId,
                'type' => $type
            ]);
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al actualizar autorización.'
            ]);
        }
    }

    private function saveAuth($remissionId, $type, $status)
    {
        VntOrderAuthorization::create([
            'remission_id' => $remissionId,
            'auth_type' => $type,
            'status' => $status,
            'user_id' => auth()->id()
        ]);
    }

    public function setFilter($filter)
    {
        $this->activeFilter = ($this->activeFilter === $filter) ? '' : $filter;
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'activeFilter', 'searchNit', 'searchName', 'searchQuote', 'showAdvancedSearch']);
        $this->fromDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $this->toDate = Carbon::now()->addDays(7)->format('Y-m-d');
        $this->resetPage();
    }

    public function openObservationsModal($remissionId)
    {
        $this->ensureTenantConnection();
        $this->dispatch('openObservations', referenceId: $remissionId, referenceType: 'remission')->to('tenant.components.observations-modal');
    }

    public function openJustificacionModal($remissionId)
    {
        $this->ensureTenantConnection();
        $this->selectedRemissionId = $remissionId;
        $this->isDesconfirmarPago = false; // Resetear flag
        
        // Cargar observación previa si existe
        $observation = \App\Models\Tenant\Sales\VntObservation::where('reference_id', $remissionId)
            ->where('reference_type', 'remission')
            ->where('observation_type', 'cartera_justificacion')
            ->first();
            
        $this->justificacionText = $observation ? $observation->observation : '';
        $this->showJustificacionModal = true;
    }

    public function saveJustificacion()
    {
        $this->ensureTenantConnection();
        
        if (!$this->selectedRemissionId) return;

        if (empty(trim($this->justificacionText))) {
            $this->dispatch('show-toast', ['type' => 'warning', 'message' => 'La justificación es obligatoria.']);
            return;
        }

        $finalObservation = $this->justificacionText;

        if ($this->isDesconfirmarPago) {
            // Obtener nombre del usuario y fecha para trazabilidad
            $userName = auth()->user()->name;
            $date = now()->format('Y-m-d H:i');
            
            // Cargar observación existente para concatenar
            $existing = \App\Models\Tenant\Sales\VntObservation::where('reference_id', $this->selectedRemissionId)
                ->where('reference_type', 'remission')
                ->where('observation_type', 'cartera_justificacion')
                ->first();
            
            $prefix = "\n--- DESCONFIRMADO POR {$userName} ({$date}) ---\n";
            $finalObservation = ($existing ? $existing->observation : "") . $prefix . $this->justificacionText;

            // Guardar el registro de desautorización en vnt_order_authorizations
            $this->saveAuth($this->selectedRemissionId, 'pago', false);
        }

        \App\Models\Tenant\Sales\VntObservation::updateOrCreate(
            [
                'reference_id' => $this->selectedRemissionId,
                'reference_type' => 'remission',
                'observation_type' => 'cartera_justificacion'
            ],
            [
                'observation' => $finalObservation,
                'userId' => auth()->id()
            ]
        );

        $this->showJustificacionModal = false;
        $this->justificacionText = '';
        $this->selectedRemissionId = null;
        $this->isDesconfirmarPago = false;

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Acción realizada correctamente.'
        ]);
    }

    /**
     * Abre el modal de carga de soporte de pago para una remisión.
     */
    public function openUploadModal($remissionId)
    {
        $this->ensureTenantConnection();
        $this->selectedRemissionId = $remissionId;
        $this->proofPaymentFile = null;
        $this->showUploadModal = true;
    }

    /**
     * Guarda el soporte de pago en el storage y actualiza la base de datos tenant.
     */
    public function saveProofPayment()
    {
        $this->ensureTenantConnection();

        if (!$this->selectedRemissionId) {
            return;
        }

        $this->validate([
            'proofPaymentFile' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'proofPaymentFile.required' => 'El soporte de pago es obligatorio.',
            'proofPaymentFile.file' => 'El archivo no es válido.',
            'proofPaymentFile.mimes' => 'El soporte debe ser un archivo de tipo JPG, JPEG, PNG o PDF.',
            'proofPaymentFile.max' => 'El archivo no debe pesar más de 5MB.',
        ]);

        try {
            DB::connection('tenant')->beginTransaction();

            $remission = InvRemissions::findOrFail($this->selectedRemissionId);

            // Almacenar archivo físicamente en disco public (storage/app/public/proof_payments)
            $path = $this->proofPaymentFile->store('proof_payments', 'public');

            // Actualizar en la base de datos del tenant
            $remission->update([
                'proof_payment' => $path
            ]);

            DB::connection('tenant')->commit();

            $this->showUploadModal = false;
            $this->proofPaymentFile = null;
            $this->selectedRemissionId = null;

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Soporte de pago cargado exitosamente.'
            ]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('❌ Error al guardar soporte de pago', [
                'error' => $e->getMessage(),
                'remissionId' => $this->selectedRemissionId
            ]);

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al guardar el soporte: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cancela la carga del soporte de pago y resetea las variables.
     */
    public function cancelUpload()
    {
        $this->showUploadModal = false;
        $this->proofPaymentFile = null;
        $this->selectedRemissionId = null;
        $this->resetErrorBag();
    }


    /**
     * Imprime la cotización/remisión asociada a la OP de cartera.
     * Reutiliza la misma lógica de impresión que el módulo de Cotizaciones.
     */
    public function printQuote($quoteId)
    {
        $this->ensureTenantConnection();
        $this->initializeCompanyConfiguration();

        try {
            $quote = VntQuote::findOrFail($quoteId);
            $quote->load(['detalles', 'detalles.item', 'customer.company', 'customer.warehouse.city']);

            $company = $this->getCompanyInfo($quote);

            $tableName     = ($quote->status === 'REMISIÓN') ? 'inv_detail_remissions' : 'vnt_detail_quotes';
            $tableNameId   = ($quote->status === 'REMISIÓN') ? 'remissionId' : 'quoteId';

            $totalWeight = DB::connection('tenant')
                ->table($tableName)
                ->join('inv_items_dimensions', $tableName . '.itemId', '=', 'inv_items_dimensions.item_id')
                ->where($tableName . '.' . $tableNameId, $quoteId)
                ->sum(DB::raw('inv_items_dimensions.weight * ' . $tableName . '.quantity'));

            $observations = DB::connection('tenant')
                ->table('inv_remissions')
                ->where('quoteId', $quoteId)
                ->select('observations_delivery', 'obs')
                ->first();

            $printFormat   = $this->getPrintCopiesLimit();
            $documentTitle = ($quote->status === 'REMISIÓN') ? 'REMISIÓN' : 'COTIZACIÓN';

            $data = [
                'quote'                 => $quote,
                'customer'              => $quote->customer,
                'company'               => $company,
                'documentTitle'         => $documentTitle,
                'showQR'                => true,
                'defaultObservations'   => '',
                'totalWeight'           => $totalWeight,
                'observations_delivery' => $observations->observations_delivery ?? null,
                'obs'                   => $observations->obs ?? null,
                'showValues'            => true,
            ];

            $viewName = ($printFormat === 1)
                ? 'livewire.tenant.quoter.print.print-carta'
                : 'livewire.tenant.quoter.print.print-pos';

            $html         = view($viewName, $data)->render();
            $tempFileName = 'quote_' . $quoteId . '_' . time() . '.html';
            $tempPath     = storage_path('app/temp/' . $tempFileName);

            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            file_put_contents($tempPath, $html);

            $printUrl = route('quoter.print.temp', ['file' => $tempFileName]);

            $this->dispatch('open-print-window', [
                'url'    => $printUrl,
                'format' => $printFormat === 1 ? 'carta' : 'pos'
            ]);

            $this->dispatch('show-toast', [
                'type'    => 'success',
                'message' => 'Documento #' . $quote->consecutive . ' preparado para imprimir.'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ CarteraList::printQuote error', ['error' => $e->getMessage()]);
            $this->dispatch('show-toast', [
                'type'    => 'error',
                'message' => 'Error al generar la impresión: ' . $e->getMessage()
            ]);
        }
    }


    /**
     * Determina el formato de impresión configurado.
     * Opción 3: 0 = POS (tirilla), 1 = Carta (institucional)
     */
    private function getPrintCopiesLimit(): int
    {
        try {
            $value = $this->getOptionValue(3);
            return $value ?? 0;
        } catch (\Exception $e) {
            return 0; // Default POS
        }
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $query = InvRemissions::with(['authorizations', 'quote', 'invoice.payments.methodPayment', 'details', 'methodPayment', 'deliveryTypeModel']);

        if (empty($this->activeFilter)) {
            $query->whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59']);
        }

        $query->latest();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('consecutive', 'like', "%{$this->search}%")
                  ->orWhereHas('quote.customer', function($cq) {
                      $cq->where(function($sub) {
                          $sub->where('firstName', 'like', "%{$this->search}%")
                              ->orWhere('lastName', 'like', "%{$this->search}%")
                              ->orWhereHas('company', function($cc) {
                                  $cc->where('businessName', 'like', "%{$this->search}%");
                              });
                      });
                  });
            });
        }

        // Filtros Búsqueda Avanzada
        if ($this->searchNit) {
            $query->whereHas('quote.customer.company', function($q) {
                $q->where('identification', 'like', "%{$this->searchNit}%");
            });
        }

        if ($this->searchName) {
            $query->whereHas('quote.customer.company', function($q) {
                $q->where('businessName', 'like', "%{$this->searchName}%")
                    ->orWhere('firstName', 'like', "%{$this->searchName}%")
                    ->orWhere('secondName', 'like', "%{$this->searchName}%")
                    ->orWhere('lastName', 'like', "%{$this->searchName}%")
                    ->orWhere('secondLastName', 'like', "%{$this->searchName}%");
            });
        }

        if ($this->searchQuote) {
            $query->whereHas('quote', function($q) {
                $q->where('consecutive', 'like', "%{$this->searchQuote}%");
            });
        }

        // Filtro por tarjetas (Lógica de "Solo los que tienen este estado como ÚLTIMO registro")
        if ($this->activeFilter) {
            if ($this->activeFilter === 'anulados') {
                $query->where('status', 'ANULADO');
            } elseif ($this->activeFilter === 'pendientes') {
                $query->where('status', '!=', 'ANULADO')
                      ->whereDoesntHave('authorizations', function($q) {
                          $q->where('status', 1);
                      });
            } else {
                $query->whereHas('authorizations', function($q) {
                    $q->where('auth_type', $this->activeFilter)
                      ->where('status', 1)
                      ->whereRaw('id = (SELECT max(id) FROM vnt_order_authorizations as a2 WHERE a2.remission_id = vnt_order_authorizations.remission_id AND a2.auth_type = ?)', [$this->activeFilter]);
                });
            }
        }

        // Métricas (Reflejan el estado actual basado en el último registro de trazabilidad)
        $metrics = [
            'empaque' => DB::connection('tenant')->table('vnt_order_authorizations as a1')
                ->where('auth_type', 'empaque')
                ->where('status', 1)
                ->whereRaw('id = (SELECT max(id) FROM vnt_order_authorizations as a2 WHERE a2.remission_id = a1.remission_id AND a2.auth_type = "empaque")')
                ->count(),
            'despacho' => DB::connection('tenant')->table('vnt_order_authorizations as a1')
                ->where('auth_type', 'despacho')
                ->where('status', 1)
                ->whereRaw('id = (SELECT max(id) FROM vnt_order_authorizations as a2 WHERE a2.remission_id = a1.remission_id AND a2.auth_type = "despacho")')
                ->count(),
            'pago' => DB::connection('tenant')->table('vnt_order_authorizations as a1')
                ->where('auth_type', 'pago')
                ->where('status', 1)
                ->whereRaw('id = (SELECT max(id) FROM vnt_order_authorizations as a2 WHERE a2.remission_id = a1.remission_id AND a2.auth_type = "pago")')
                ->count(),
            'anulados' => InvRemissions::where('status', 'ANULADO')->count(),
            'pendientes' => InvRemissions::where('status', '!=', 'ANULADO')
                ->whereDoesntHave('authorizations', function($q) {
                    $q->where('status', 1);
                })->count(),
        ];

        return view('livewire.tenant.cartera.cartera-list', [
            'remissions' => $query->paginate($this->perPage),
            'metrics' => $metrics
        ])->layout('layouts.app');
    }

    /**
     * Asegura que la conexión 'tenant' esté configurada
     */
    /**
     * Obtiene la información de la empresa para los documentos de impresión.
     */
    private function getCompanyInfo($quote = null): object
    {
        try {
            $userId = auth()->id();
            if (!$userId) throw new \Exception('Usuario no autenticado');

            $companyData = DB::connection('central')->table('users as u')
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
                    'v.billingEmail'
                ])
                ->first();

            if ($companyData) return $companyData;

            throw new \Exception('Datos de empresa no encontrados');
        } catch (\Exception $e) {
            Log::error('CarteraList::getCompanyInfo error: ' . $e->getMessage());
            return (object) [
                'businessName'   => 'EMPRESA',
                'billingAddress' => 'N/A',
                'city'           => 'N/A',
                'acronym'        => 'NIT',
                'identification' => 'N/A',
                'billingEmail'   => 'N/A'
            ];
        }
    }

    /**
     * Asegura que la conexión 'tenant' esté configurada usando el TenantManager
     */
    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }

        // Importante: Usar la conexión central para buscar el tenant
        $tenant = \App\Models\Auth\Tenant::on('central')->find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            return redirect()->route('tenant.select');
        }

        $tenantManager = app(\App\Services\Tenant\TenantManager::class);
        $tenantManager->setConnection($tenant);
        
        if (function_exists('tenancy')) {
            tenancy()->initialize($tenant);
        }
    }

}


