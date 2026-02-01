<?php

namespace App\Livewire\Tenant\PettyCash;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;
use App\Models\Tenant\MethodPayments\VntMethodPayMents;
use App\Models\Tenant\PettyCash\PettyCash;
use Illuminate\Support\Facades\Log;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Models\Tenant\Quoter\VntQuote;

class PaymentQuote extends Component
{
    // Datos de la cotización
    public $quoteId;
    public $quoteCustumer;
    public $quoteNumber;
    public $quoteSubtotal;
    public $quoteTaxes;
    public $quoteTotal;

    // Datos de anticipos
    public $advances = [];
    public $totalAdvances = 0;

    // Métodos de pago disponibles (solo 4 principales)
    public $paymentMethods = [
        'efectivo' => ['name' => 'EFECTIVO', 'value' => 0, 'selected' => false],
        'nequi' => ['name' => 'NEQUI', 'value' => 0, 'selected' => false],
        'daviplata' => ['name' => 'DAVIPLATA', 'value' => 0, 'selected' => false],
        'tarjeta' => ['name' => 'TARJETA', 'value' => 0, 'selected' => false],
    ];

    public $currentMethod = 'efectivo';

    // Cálculos dinámicos
    public $totalPaid = 0;
    public $remainingBalance = 0;

    // Estado del pago
    public $willBeCredit = false;
    public $observations = '';

    // Caja activa
    public $activePettyCash;

    // Estado de validaciones
    public $canProceedToPayment = false;

    public function updating($name, $value)
    {
        // Interceptar actualizaciones de valores de métodos de pago
        if (str_contains($name, 'paymentMethods.') && str_ends_with($name, '.value')) {
            Log::info('🔔 updating() disparado para método de pago', [
                'field' => $name,
                'oldValue' => data_get($this->paymentMethods, str_replace(['paymentMethods.', '.value'], ['', ''], $name) . '.value'),
                'newValue' => $value
            ]);
            return max(0, (float) ($value ?? 0));
        }
        return $value;
    }

    public function updated($name)
    {
        // Después de actualizar cualquier valor de método de pago, recalcular
        if (str_contains($name, 'paymentMethods.') && str_ends_with($name, '.value')) {
            Log::info('✅ updated() disparado para método de pago', [
                'field' => $name,
                'newValue' => data_get($this->paymentMethods, str_replace(['paymentMethods.', '.value'], ['', ''], $name) . '.value'),
                'allPaymentMethods' => $this->paymentMethods
            ]);

            $this->autoDistributePayments();
            $this->calculateBalances();
        }
    }

    public function boot()
    {
        // Asegurar conexión tenant en cada request de Livewire
        $this->ensureTenantConnection();
    }

    public function mount($quoteId = null)
    {
        // Asegurar conexión tenant
        $this->ensureTenantConnection();

        $this->quoteId = $quoteId ? (int) $quoteId : 1;

        // Cargar datos de la cotización
        $this->loadQuoteData();

        // Verificar que haya una caja abierta
        $this->checkActivePettyCash();

        // Los métodos de pago ya están definidos estáticamente

        // Simular anticipos existentes (opcional)
        $this->loadAdvances();

        // Calcular balances iniciales
        $this->calculateBalances();
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            return redirect()->route('tenant.select');
        }

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }

    private function loadQuoteData()
    {
        try {
            $quote = VntQuote::with(['customer', 'detalles.item'])
                ->where('id', $this->quoteId)
                ->first();

            if (!$quote) {
                session()->flash('error', 'Cotización no encontrada.');
                $this->setDefaultQuoteData();
                return;
            }

            // Cargar datos del cliente (no almacenar el modelo)
            $this->quoteCustumer = $quote->customer_name ?? 'Cliente no encontrado';

            // Cargar número de cotización
            $this->quoteNumber = 'COT-' . str_pad($quote->consecutive ?? 0, 6, '0', STR_PAD_LEFT);

            // Calcular totales desde los detalles
            $this->calculateQuoteTotals($quote);

        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar la cotización: ' . $e->getMessage());
            $this->setDefaultQuoteData();
        }
    }

    private function calculateQuoteTotals($quote)
    {
        $subtotal = 0;
        $totalTaxes = 0;

        // Calcular totales imitando la lógica de Alegra para coincidir exactamente
        foreach ($quote->detalles as $detalle) {
            $priceWithIva = $detalle->value;

            // Calcular como Alegra: subtotal redondeado a entero, luego aplicar IVA
            $priceBase = round($priceWithIva / 1.19, 0); // Subtotal como Alegra
            $alegraStyleTotal = $priceBase * 1.19;       // Total como Alegra

            $lineSubtotal = $priceBase * $detalle->quantity;
            $lineTax = ($alegraStyleTotal - $priceBase) * $detalle->quantity;

            $subtotal += $lineSubtotal;
            $totalTaxes += $lineTax;
        }

        $this->quoteSubtotal = $subtotal;
        $this->quoteTaxes = $totalTaxes;
        $this->quoteTotal = $subtotal + $totalTaxes;
    }

    private function setDefaultQuoteData()
    {
        // Datos por defecto si no se puede cargar la cotización
        $this->quoteCustumer = 'CLIENTE DE PRUEBA';
        $this->quoteNumber = 'COT-000001';
        $this->quoteSubtotal = 100000;
        $this->quoteTaxes = 19000;
        $this->quoteTotal = 119000;
    }

    private function checkActivePettyCash()
    {
        try {
            $pettyCash = PettyCash::where('status', 1)->first();

            if (!$pettyCash) {
                session()->flash('error', 'No hay una caja abierta. Debe abrir una caja antes de procesar pagos.');
                return false;
            }

            // Convertir a array para evitar problemas de hidratación
            $this->activePettyCash = $pettyCash->toArray();
            return true;

        } catch (\Exception $e) {
            session()->flash('error', 'Error al verificar la caja: ' . $e->getMessage());
            return false;
        }
    }


    private function loadAdvances()
    {
        // Aquí simularemos algunos anticipos de ejemplo
        // En la implementación real, esto vendrá de la base de datos
        $this->advances = [
            // ['id' => 1, 'method_name' => 'EFECTIVO', 'value' => 50000, 'date' => '2024-12-01'],
            // ['id' => 2, 'method_name' => 'TRANSFERENCIA', 'value' => 30000, 'date' => '2024-12-02'],
        ];

        $this->totalAdvances = collect($this->advances)->sum('value');
    }

    // Métodos obsoletos eliminados - ahora usamos paymentMethods array

    public function calculateBalances()
    {
        $totalFromMethods = 0;
        foreach ($this->paymentMethods as $method) {
            $totalFromMethods += (float) ($method['value'] ?? 0);
        }
        $this->totalPaid = $this->totalAdvances + $totalFromMethods;
        $this->remainingBalance = $this->quoteTotal - $this->totalPaid;

        // Determinar si puede proceder al pago
        $oldCanProceed = $this->canProceedToPayment;
        // Lógica temporal más permisiva para debugging
        $this->canProceedToPayment = $this->totalPaid > 0;

        Log::info('💰 calculateBalances ejecutado', [
            'totalFromMethods' => $totalFromMethods,
            'totalAdvances' => $this->totalAdvances,
            'totalPaid' => $this->totalPaid,
            'quoteTotal' => $this->quoteTotal,
            'remainingBalance' => $this->remainingBalance,
            'oldCanProceed' => $oldCanProceed,
            'newCanProceed' => $this->canProceedToPayment,
            'comparison_details' => [
                'totalPaid_exactly' => $this->totalPaid,
                'quoteTotal_exactly' => $this->quoteTotal,
                'totalPaid_type' => gettype($this->totalPaid),
                'quoteTotal_type' => gettype($this->quoteTotal),
                'comparison_result' => $this->totalPaid >= $this->quoteTotal,
                'difference' => $this->totalPaid - $this->quoteTotal
            ]
        ]);
    }

    public function updateMethodValue($method, $value)
    {
        $value = max(0, (float) ($value ?? 0));
        $this->paymentMethods[$method]['value'] = $value;

        // Auto-balance: distribuir el resto automáticamente
        $this->autoDistributePayments();
        $this->calculateBalances();
    }

    public function autoDistributeFromCash()
    {
        // Calcular total de todos los métodos EXCEPTO efectivo
        $totalOtherMethods = 0;
        foreach ($this->paymentMethods as $key => $method) {
            if ($key !== 'efectivo') {
                $totalOtherMethods += (float) ($method['value'] ?? 0);
            }
        }

        // El efectivo debe ser el total de venta MENOS los otros métodos
        $cashAmount = $this->quoteTotal - $totalOtherMethods;

        // No permitir efectivo negativo
        if ($cashAmount < 0) {
            $cashAmount = 0;
            // Recalcular otros métodos proporcionalmente
            $this->redistributePayments($totalOtherMethods);
        }

        $this->paymentMethods['efectivo']['value'] = max(0, $cashAmount);
    }

    public function autoDistributePayments()
    {
        Log::info('🔄 autoDistributePayments ejecutado', [
            'paymentMethods' => $this->paymentMethods,
            'quoteTotal' => $this->quoteTotal
        ]);

        // Calcular total actual de todos los métodos
        $totalCurrentPayments = 0;
        foreach ($this->paymentMethods as $method) {
            $totalCurrentPayments += (float) ($method['value'] ?? 0);
        }

        Log::info('💳 Calculando pagos', [
            'totalCurrentPayments' => $totalCurrentPayments,
            'quoteTotal' => $this->quoteTotal,
            'exceedsTotal' => $totalCurrentPayments > $this->quoteTotal
        ]);

        // Si el total excede la venta, ajustar proporcionalmente
        if ($totalCurrentPayments > $this->quoteTotal) {
            $this->redistributePayments($totalCurrentPayments);
        }
        // Si es menor al total, no hacer nada (permitir combinaciones manuales)

        // IMPORTANTE: Recalcular balances para actualizar canProceedToPayment
        $this->calculateBalances();

        Log::info('✅ Balances actualizados', [
            'totalPaid' => $this->totalPaid,
            'remainingBalance' => $this->remainingBalance,
            'canProceedToPayment' => $this->canProceedToPayment
        ]);
    }

    private function redistributePayments($overAmount)
    {
        // Si el total excede la venta, redistribuir proporcionalmente TODOS los métodos
        $scale = $this->quoteTotal / $overAmount;

        foreach ($this->paymentMethods as $key => $method) {
            if (((float) ($method['value'] ?? 0)) > 0) {
                $this->paymentMethods[$key]['value'] = round(((float) ($method['value'] ?? 0)) * $scale);
            }
        }
    }

    public function selectMethod($method)
    {
        $this->currentMethod = $method;
    }

    public function payTotalWithCurrentMethod()
    {
        // Encontrar el total actual pagado
        $totalCurrentlyPaid = array_sum(array_column($this->paymentMethods, 'value'));

        // Si no hay dinero en ningún lado, usar el total de la venta
        if ($totalCurrentlyPaid == 0) {
            $totalCurrentlyPaid = $this->quoteTotal;
        }

        // Limpiar TODOS los métodos
        foreach ($this->paymentMethods as $key => $method) {
            $this->paymentMethods[$key]['value'] = 0;
        }

        // Mover TODO el dinero al siguiente método automáticamente
        $methods = array_keys($this->paymentMethods);
        $currentIndex = array_search($this->currentMethod, $methods);
        $nextIndex = ($currentIndex + 1) % count($methods);
        $nextMethod = $methods[$nextIndex];

        // Asignar el dinero al siguiente método
        $this->paymentMethods[$nextMethod]['value'] = $totalCurrentlyPaid;

        // Cambiar la selección al nuevo método
        $this->currentMethod = $nextMethod;

        $this->calculateBalances();
    }

    public function nextMethod()
    {
        // Solo navegar, NO transferir dinero
        $methods = array_keys($this->paymentMethods);
        $currentIndex = array_search($this->currentMethod, $methods);
        $nextIndex = ($currentIndex + 1) % count($methods);
        $this->currentMethod = $methods[$nextIndex];
    }

    public function previousMethod()
    {
        // Solo navegar, NO transferir dinero
        $methods = array_keys($this->paymentMethods);
        $currentIndex = array_search($this->currentMethod, $methods);
        $prevIndex = ($currentIndex - 1 + count($methods)) % count($methods);
        $this->currentMethod = $methods[$prevIndex];
    }

    // Métodos obsoletos eliminados - funcionalidad movida a payTotalWithCurrentMethod()

    // Método obsoleto eliminado

    public function confirmPayment()
    {
        // Validaciones finales
        if (!$this->canProceedToPayment) {
            $this->dispatch('showAlert', 'Error: Debe ingresar al menos un pago para proceder.');
            return;
        }

        if ($this->remainingBalance < 0) {
            $this->dispatch('showAlert', 'Error: El total pagado excede el valor de la cotización.');
            return;
        }

        if ($this->remainingBalance > 0) {
            $this->dispatch('showAlert', 'Advertencia: Queda un saldo pendiente de $' . number_format($this->remainingBalance, 0, ',', '.'));
            return;
        }

        try {
            // Preparar datos de pago para enviar a la facturación
            $paymentData = [];
            foreach ($this->paymentMethods as $key => $method) {
                if (((float) ($method['value'] ?? 0)) > 0) {
                    $paymentData[] = [
                        'method' => $method['name'],
                        'value' => (float) $method['value']
                    ];
                }
            }

            // Log del pago procesado
            Log::info('💰 Pago confirmado, procediendo a facturación', [
                'quote_id' => $this->quoteId,
                'total_paid' => $this->totalPaid,
                'payment_methods' => $paymentData,
                'remaining_balance' => $this->remainingBalance,
                'petty_cash_id' => $this->activePettyCash['id'] ?? null
            ]);

            // Crear resumen del pago para mostrar al usuario
            $metodosUsados = [];
            foreach ($paymentData as $method) {
                $metodosUsados[] = $method['method'] . ': $' . number_format($method['value'], 0, ',', '.');
            }

            $resumen = "PAGO CONFIRMADO\n\n";
            $resumen .= "Total: $" . number_format($this->quoteTotal, 0, ',', '.') . "\n";
            $resumen .= "Métodos de pago:\n" . implode("\n", $metodosUsados);
            $resumen .= "\n\n¡Procediendo a generar factura...!";

            // Mostrar confirmación
            $this->dispatch('showAlert', $resumen);

            // NUEVO: Procesar factura con los datos de pago
            return $this->processInvoice($paymentData);

        } catch (\Exception $e) {
            Log::error('❌ Error en confirmPayment', [
                'quote_id' => $this->quoteId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('showAlert', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Procesar la factura después del pago confirmado
     */
    public function processInvoice(array $paymentData)
    {
        // Redirigir al cotizador con los datos de pago para procesar la factura
        Log::info('🔄 Redirigiendo al cotizador para procesar factura', [
            'quote_id' => $this->quoteId,
            'payment_data_count' => count($paymentData)
        ]);

        // Guardar datos de pago en sesión para pasarlos al cotizador
        session(['payment_data_for_quote_' . $this->quoteId => $paymentData]);

        // Redirigir de vuelta al cotizador en modo edición para procesar la factura
        return redirect()->route('tenant.quoter.products.desktop.edit', ['quoteId' => $this->quoteId])
                        ->with('process_invoice_after_payment', true);
    }

    public function resetPayment()
    {
        foreach ($this->paymentMethods as $key => $method) {
            $this->paymentMethods[$key]['value'] = 0;
        }
        $this->willBeCredit = false;
        $this->observations = '';
        $this->calculateBalances();
        session()->flash('info', 'Formulario de pago reiniciado.');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.tenant.petty-cash.payment-quote');
    }
}