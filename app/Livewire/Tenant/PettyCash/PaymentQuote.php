<?php

namespace App\Livewire\Tenant\PettyCash;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Session;
use App\Models\Tenant\PettyCash\VntMethodPayments;
use App\Models\Tenant\PettyCash\VntPettyCash;

class PaymentQuote extends Component
{
    // Datos de la cotización (simulados por ahora)
    public $quoteId;
    public $quoteCustumer = 'CLIENTE DE PRUEBA';
    public $quoteNumber = 'COT-2024-001';
    public $quoteSubtotal = 100000;
    public $quoteTaxes = 19000;
    public $quoteTotal = 119000;

    // Datos de anticipos
    public $advances = [];
    public $totalAdvances = 0;

    // Métodos de pago disponibles
    public $availablePaymentMethods = [];

    // Métodos de pago seleccionados
    public $selectedPaymentMethods = [];
    public $paymentValues = [];

    // Cálculos dinámicos
    public $totalPaid = 0;
    public $remainingBalance = 0;

    // Estado del pago
    public $willBeCredit = false;
    public $observations = '';

    // Caja activa
    public $activePettyCash;

    // Estado de validaciones
    public $showPaymentMethods = false;
    public $canProceedToPayment = false;

    public function mount($quoteId = null)
    {
        $this->quoteId = $quoteId ?? 1;

        // Verificar que haya una caja abierta
        $this->checkActivePettyCash();

        // Cargar métodos de pago disponibles
        $this->loadPaymentMethods();

        // Simular anticipos existentes (opcional)
        $this->loadAdvances();

        // Calcular balances iniciales
        $this->calculateBalances();
    }

    private function checkActivePettyCash()
    {
        $this->activePettyCash = VntPettyCash::where('status', 1)->first();

        if (!$this->activePettyCash) {
            session()->flash('error', 'No hay una caja abierta. Debe abrir una caja antes de procesar pagos.');
            return false;
        }

        return true;
    }

    private function loadPaymentMethods()
    {
        $this->availablePaymentMethods = VntMethodPayments::where('status', 1)
            ->orderBy('name')
            ->get()
            ->toArray();
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

    public function addAdvance()
    {
        // Esta función se llamará cuando se quiera agregar un nuevo anticipo
        // Por ahora solo simularemos la lógica
        $this->showPaymentMethods = true;
    }

    public function togglePaymentMethod($methodId)
    {
        $key = array_search($methodId, $this->selectedPaymentMethods);

        if ($key !== false) {
            // Remover método de pago
            unset($this->selectedPaymentMethods[$key]);
            unset($this->paymentValues[$methodId]);
            $this->selectedPaymentMethods = array_values($this->selectedPaymentMethods);
        } else {
            // Agregar método de pago
            $this->selectedPaymentMethods[] = $methodId;
            $this->paymentValues[$methodId] = 0;
        }

        $this->showPaymentMethods = !empty($this->selectedPaymentMethods);
        $this->calculateBalances();
    }

    public function updatedPaymentValues($value, $methodId)
    {
        // Validar que el valor sea numérico y positivo
        if (!is_numeric($value) || $value < 0) {
            $this->paymentValues[$methodId] = 0;
        } else {
            $this->paymentValues[$methodId] = (float) $value;
        }

        $this->calculateBalances();
    }

    public function calculateBalances()
    {
        $this->totalPaid = $this->totalAdvances + array_sum($this->paymentValues);
        $this->remainingBalance = $this->quoteTotal - $this->totalPaid;

        // Determinar si puede proceder al pago
        $this->canProceedToPayment = $this->totalPaid > 0;
    }

    public function distributeEqually()
    {
        $selectedMethods = count($this->selectedPaymentMethods);

        if ($selectedMethods > 0 && $this->remainingBalance > 0) {
            $valuePerMethod = round($this->remainingBalance / $selectedMethods, 2);

            foreach ($this->selectedPaymentMethods as $methodId) {
                $this->paymentValues[$methodId] = $valuePerMethod;
            }

            // Ajustar la diferencia por redondeo en el primer método
            $totalDistributed = $valuePerMethod * $selectedMethods;
            $difference = $this->remainingBalance - $totalDistributed;

            if ($difference != 0 && !empty($this->selectedPaymentMethods)) {
                $this->paymentValues[$this->selectedPaymentMethods[0]] += $difference;
            }

            $this->calculateBalances();
        }
    }

    public function payTotalWithCash()
    {
        // Buscar el método "EFECTIVO"
        $cashMethod = collect($this->availablePaymentMethods)->firstWhere('name', 'EFECTIVO');

        if ($cashMethod && $this->remainingBalance > 0) {
            $this->selectedPaymentMethods = [$cashMethod['id']];
            $this->paymentValues = [$cashMethod['id'] => $this->remainingBalance];
            $this->showPaymentMethods = true;
            $this->calculateBalances();
        }
    }

    public function getSelectedMethodName($methodId)
    {
        $method = collect($this->availablePaymentMethods)->firstWhere('id', $methodId);
        return $method ? $method['name'] : 'Método no encontrado';
    }

    public function confirmPayment()
    {
        // Validaciones finales
        if (!$this->canProceedToPayment) {
            session()->flash('error', 'Debe ingresar al menos un pago para proceder.');
            return;
        }

        if ($this->remainingBalance < 0) {
            session()->flash('error', 'El total pagado no puede exceder el valor de la cotización.');
            return;
        }

        // Si queda saldo y no se ha decidido sobre crédito
        if ($this->remainingBalance > 0 && !$this->willBeCredit) {
            session()->flash('warning', 'Queda un saldo pendiente. ¿Desea dejarlo a crédito?');
            return;
        }

        // Aquí iría la lógica de guardado en base de datos
        // Por ahora solo mostramos un mensaje de éxito
        session()->flash('success', 'Pago procesado correctamente. (Modo simulación - no se guardó en base de datos)');

        // Log de la transacción simulada
        \Log::info('Pago simulado procesado:', [
            'quote_id' => $this->quoteId,
            'total_paid' => $this->totalPaid,
            'payment_methods' => $this->paymentValues,
            'remaining_balance' => $this->remainingBalance,
            'is_credit' => $this->willBeCredit,
            'observations' => $this->observations,
            'petty_cash_id' => $this->activePettyCash->id ?? null
        ]);
    }

    public function resetPayment()
    {
        $this->selectedPaymentMethods = [];
        $this->paymentValues = [];
        $this->willBeCredit = false;
        $this->observations = '';
        $this->showPaymentMethods = false;
        $this->calculateBalances();
        session()->flash('info', 'Formulario de pago reiniciado.');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.tenant.petty-cash.payment-quote');
    }
}