<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Tenant\Sales\VntObservation;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ObservationsModal extends Component
{
    public $isOpen = false;
    public $referenceId = null;
    public $consecutive = null;
    public $referenceType = 'remission';
    public $title = 'Observaciones del Pedido';
    public $orderObservations = '';
    public $deliveryObservations = '';
    
    // Diccionario de observaciones: type => content
    public $observationData = [
        'flete_justification' => '',
        'annulment_reason' => '',
        'delivery_obs' => '',
        'print_obs' => '',
        'reprint' => '',
        'delivered_obs' => '',
        'impossibility_obs' => '',
        'payment_obs' => '',
        'no_stock_obs' => '',
        'cartera_justificacion' => '',
        'invoice_observation' => ''
    ];

    public $deliveryType = ''; // Para mostrar el tipo de entrega si aplica

    /**
     * Asegura la conexión con el tenant
     */
    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
    }

    #[On('openObservations')]
    public function open($referenceId, $referenceType = 'remission', $title = null)
    {
        $this->ensureTenantConnection();
        
        $this->referenceId = $referenceId;
        $this->referenceType = $referenceType;
        if ($title) $this->title = $title;

        $this->loadObservations();
        $this->isOpen = true;
    }

    /**
     * Carga las observaciones de la base de datos
     */
    public function loadObservations()
    {
        $this->ensureTenantConnection();

        // Reset data
        foreach ($this->observationData as $key => $value) {
            $this->observationData[$key] = '';
        }

        if (!$this->referenceId) return;

        $observations = VntObservation::where('reference_id', $this->referenceId)
            ->where('reference_type', $this->referenceType)
            ->get();

        foreach ($observations as $obs) {
            if (isset($this->observationData[$obs->observation_type])) {
                // Si es reimpresión, concatenamos con las anteriores si existen
                if ($obs->observation_type === 'reprint') {
                    $this->observationData['reprint'] .= ($this->observationData['reprint'] ? "\n" : "") . $obs->observation;
                } else {
                    $this->observationData[$obs->observation_type] = $obs->observation;
                }
            }
        }

        // Cargar tipo de entrega y consecutivo si es una remisión
        if ($this->referenceType === 'remission') {
            $remission = \App\Models\Tenant\Remissions\InvRemissions::with(['deliveryTypeModel', 'invoice'])->find($this->referenceId);
            $this->deliveryType = $remission?->deliveryTypeModel?->name ?? 'N/A';
            $this->consecutive = $remission?->consecutive;
            $this->orderObservations = $remission?->obs ?? '';
            $this->deliveryObservations = $remission?->observations_delivery ?? '';

            // Cargar observaciones específicas de cada forma de pago desde payment_details
            $paymentDetails = $remission?->payment_details;
            if (is_string($paymentDetails)) {
                $paymentDetails = json_decode($paymentDetails, true);
            }
            if (is_array($paymentDetails) && count($paymentDetails) > 0) {
                $paymentObsList = [];
                $allMethods = \App\Models\Tenant\MethodPayments\VntMethodPayMents::on('tenant')->get()->keyBy('id');
                foreach ($paymentDetails as $pDetail) {
                    $methodName = $allMethods[$pDetail['method_payment_id'] ?? '']?->name ?? 'MÉTODO';
                    $obsValue = trim($pDetail['observation'] ?? '');
                    if ($obsValue !== '') {
                        $paymentObsList[] = "[$methodName]: $obsValue";
                    }
                }
                if (!empty($paymentObsList)) {
                    $this->observationData['payment_obs'] = implode("\n", $paymentObsList);
                } else {
                    $this->observationData['payment_obs'] = 'N/A';
                }
            }

            // Cargar observación de facturación de la factura asociada si no se cargó ya de la remisión
            if (empty($this->observationData['invoice_observation'])) {
                $invoice = $remission ? $remission->invoice : null;
                if ($invoice) {
                    $invoiceObs = VntObservation::where('reference_id', $invoice->id)
                        ->where('reference_type', 'invoice')
                        ->where('observation_type', 'invoice_observation')
                        ->first();
                    $this->observationData['invoice_observation'] = $invoiceObs ? $invoiceObs->observation : '';
                }
            }
        } else {
            $this->orderObservations = '';
            $this->deliveryObservations = '';
            $this->observationData['invoice_observation'] = '';
        }
    }

    /**
     * Guarda todas las observaciones
     */
    public function save()
    {
        $this->ensureTenantConnection();

        if (!$this->referenceId) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No se ha seleccionado un documento válido.']);
            return;
        }

        try {
            DB::connection('tenant')->beginTransaction();

            foreach ($this->observationData as $type => $content) {
                // Actualizar o crear
                VntObservation::updateOrCreate(
                    [
                        'reference_id' => $this->referenceId,
                        'reference_type' => $this->referenceType,
                        'observation_type' => $type
                    ],
                    [
                        'observation' => $content ?? '',
                        'userId' => auth()->id()
                    ]
                );
            }

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', [
                'type' => 'success', 
                'message' => 'Observaciones guardadas correctamente.'
            ]);
            
            $this->isOpen = false;

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error("Error guardando observaciones: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error', 
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function render()
    {
        return view('livewire.tenant.components.observations-modal');
    }
}
