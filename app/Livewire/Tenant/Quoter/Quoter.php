<?php

namespace App\Livewire\Tenant\Quoter;

use App\Models\Tenant\Quoter\VntQuote;
use App\Traits\HasCompanyConfiguration;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class Quoter extends Component
{
    use WithPagination, HasCompanyConfiguration;

    public $search = '';
    public $viewType = 'desktop'; // 'desktop' o 'mobile'

    protected $paginationTheme = 'bootstrap';

    public function mount($viewType = null)
    {
        // Obtener viewType desde parámetro, ruta o usar desktop por defecto
        $this->viewType = $viewType ?? request()->route('viewType', 'desktop');

        // Inicializar configuración de empresa
        $this->initializeCompanyConfiguration();

        // DEBUG: Limpiar caché para testing
        $this->clearConfigurationCache();

        // DEBUG: Log para verificar inicialización
        Log::info('🔍 Quoter mount() ejecutado', [
            'viewType' => $this->viewType,
            'currentCompanyId' => $this->currentCompanyId,
            'currentPlainId' => $this->currentPlainId,
            'configService_exists' => $this->configService ? 'YES' : 'NO'
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function nuevaCotizacion()
    {
        return redirect('/tenant/quoter/products');
    }

    public function eliminar($id)
    {
        $quote = VntQuote::find($id);
        if ($quote) {
            $quote->delete();
            session()->flash('message', 'Cotización eliminada correctamente.');
        }
    }

    /**
     * Verifica si puede imprimir (opción 3)
     */
    public function canPrint(): bool
    {
        $result = $this->isOptionEnabled(3);
        $value = $this->getOptionValue(3);

        // DEBUG: Log detallado de verificación
        Log::info('🔍 canPrint() verificación', [
            'companyId' => $this->currentCompanyId,
            'option_id' => 3,
            'result' => $result ? 'TRUE' : 'FALSE',
            'option_value' => $value,
            'configService_exists' => $this->configService ? 'YES' : 'NO',
            'method_called' => 'isOptionEnabled(3) y getOptionValue(3)'
        ]);

        return $result;
    }

    /**
     * Obtiene el límite de copias para imprimir
     */
    public function getPrintCopiesLimit(): int
    {
        $value = $this->getOptionValue(3);

        // Si value = 0: no puede imprimir
        // Si value = 1: puede imprimir 1 copia
        // Si value = 5: puede imprimir 5 copias
        return $value ?? 0;
    }

    /**
     * Método para imprimir cotización
     */
    public function printQuote($id)
    {
        session()->flash('message', 'Cotización #' . $id . ' enviada a imprimir.');
    }

    public function render()
    {
        $quotes = VntQuote::with('detalles')
            ->when($this->search, function ($query) {
                $query->where('consecutive', 'like', '%' . $this->search . '%')
                    ->orWhere('customerId', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $viewName = $this->viewType === 'mobile'
            ? 'livewire.tenant.quoter.components.quoter-mobile'
            : 'livewire.tenant.quoter.components.quoter-desktop';

        return view($viewName, [
            'quotes' => $quotes
        ]);
    }
}