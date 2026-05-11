<?php

namespace App\Livewire\Tenant\Dispatches;

use App\Models\Auth\Tenant;
use App\Models\Auth\User;
use App\Models\Tenant\Production\PrdProductionOrder;
use App\Models\Tenant\Sales\VntGuia;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class DispatchManager extends Component
{
    // Estados del Wizard
    public $step = 'carrier_selection'; // carrier_selection, scanner_active
    public $statusMessage = 'Iniciando sistema...';
    
    // Variables de sesión de escaneo
    public $selectedCarrier = null;
    public $currentUser = null;
    public $currentOP = null;
    
    // Datos de entrada
    public $scanInput = '';
    public $inputFocused = true;
    public $scannedItems = [];
    
    // Historial
    public $showHistory = false;
    public $dateFrom, $dateTo;

    public function boot()
    {
        $this->ensureTenantConnection();
        if (!$this->dateFrom) $this->dateFrom = now()->format('Y-m-d');
        if (!$this->dateTo) $this->dateTo = now()->format('Y-m-d');
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);
    }

    public function selectCarrier($carrier)
    {
        $this->selectedCarrier = $carrier;
        $this->step = 'scanner_active';
        $this->statusMessage = 'ESCANEE QR DE USUARIO';
        $this->dispatch('show-toast', type: 'success', message: "Transportadora: $carrier");
    }

    public function resetWizard()
    {
        $this->reset(['step', 'selectedCarrier', 'currentUser', 'currentOP', 'scannedItems', 'scanInput']);
        $this->statusMessage = 'Seleccione Transportadora';
    }

    public function processScan()
    {
        $this->ensureTenantConnection();
        $input = trim($this->scanInput);
        $this->scanInput = '';

        if (empty($input)) return;

        // FLUJO DE ESTADOS
        
        // 1. Si no hay usuario, el escaneo DEBE ser un usuario
        if (!$this->currentUser) {
            $this->validateUser($input);
            return;
        }

        // 2. Si hay usuario pero no hay OP, el escaneo DEBE ser una OP
        if (!$this->currentOP) {
            $this->validateOP($input);
            return;
        }

        // 3. Si hay usuario y OP, el escaneo puede ser:
        // - Otra OP (cambia contexto)
        // - Una Guía (agrega paquete)
        // - Otro Usuario (cambia usuario o guarda lote)
        
        if (preg_match('/OPX(\d+)/', $input, $matches)) {
            $this->validateOP($matches[1]);
        } else {
            // Intentar procesar como guía para la transportadora actual
            $this->processGuide($input);
        }
    }

    private function validateUser($userQr)
    {
        // En este sistema el QR del usuario es su ID numérico
        $user = User::find($userQr);

        if ($user) {
            $this->currentUser = $user;
            $this->statusMessage = "BIENVENIDO " . strtoupper($user->name) . " | ESCANEE OP";
            $this->dispatch('show-toast', type: 'success', message: "Usuario: {$user->name}");
        } else {
            $this->dispatch('show-toast', type: 'error', message: "Usuario no encontrado");
        }
    }

    private function validateOP($opInput)
    {
        // Limpiar "OPX" si viene
        $opId = str_replace('OPX', '', $opInput);
        
        $order = PrdProductionOrder::find($opId);

        if ($order) {
            // Validar estados: 18 (Empacado), 19 (Pendiente despacho)
            if (in_array($order->status, [18, 19, 4, 5])) { // Añado 4 y 5 temporalmente según los datos vistos en el SQL
                $this->currentOP = $order;
                $this->statusMessage = "OP #$opId VÁLIDA | ESCANEE GUÍA";
                $this->dispatch('show-toast', type: 'success', message: "Orden #$opId aceptada");
            } else {
                $this->dispatch('show-toast', type: 'warning', message: "OP #$opId no está en estado EMPACADO (Estado actual: {$order->status})");
            }
        } else {
            $this->dispatch('show-toast', type: 'error', message: "Orden de Producción #$opId no existe");
        }
    }

    private function processGuide($input)
    {
        $guideNumber = $input;

        // Lógica específica para Coordinadora (extraer número de guía del QR largo)
        if ($this->selectedCarrier == 'Coordinadora' && strlen($input) >= 32) {
            $guideNumber = substr($input, 18, 11);
        }

        // Agregar a la lista temporal
        $this->scannedItems[] = [
            'op' => $this->currentOP->id,
            'guide' => $guideNumber,
            'packages' => 1,
            'user_id' => $this->currentUser->id
        ];

        $this->statusMessage = "GUÍA $guideNumber REGISTRADA | SIGA ESCANEANDO";
        $this->dispatch('show-toast', type: 'success', message: "Guía agregada");
    }

    public function removeItem($index)
    {
        unset($this->scannedItems[$index]);
        $this->scannedItems = array_values($this->scannedItems);
    }

    public function saveBatch()
    {
        $this->ensureTenantConnection();

        if (empty($this->scannedItems)) return;

        try {
            DB::connection('tenant')->beginTransaction();

            foreach ($this->scannedItems as $item) {
                // 1. Crear registro de guía
                VntGuia::create([
                    'production_order_id' => $item['op'],
                    'user_id' => $item['user_id'],
                    'guide_number' => $item['guide'],
                    'package_count' => $item['packages'],
                    'carrier' => $this->selectedCarrier,
                ]);

                // 2. Actualizar estado de la OP a Despachado (20)
                PrdProductionOrder::where('id', $item['op'])->update(['status' => 20]);
            }

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', type: 'success', message: "Despachos registrados correctamente");
            $this->reset(['scannedItems', 'currentOP']);
            $this->statusMessage = "LOTE GUARDADO | ESCANEE NUEVA OP";

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->dispatch('show-toast', type: 'error', message: "Error al guardar: " . $e->getMessage());
            Log::error("❌ Error en DispatchManager::saveBatch", ['error' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $this->ensureTenantConnection();
        
        $history = $this->showHistory 
            ? VntGuia::with('user')
                ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
                ->orderBy('created_at', 'desc')
                ->get()
            : [];

        return view('livewire.tenant.dispatches.dispatch-manager', [
            'history' => $history
        ]);
    }
}
