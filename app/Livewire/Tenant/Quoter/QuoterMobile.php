<?php

namespace App\Livewire\Tenant\Quoter;

use App\Models\Tenant\Quoter\VntQuote;
use Livewire\Component;
use Livewire\WithPagination;

class QuoterMobile extends Component
{
    use WithPagination;

    public $search = '';

    protected $paginationTheme = 'bootstrap';

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

    public function render()
    {
        $quotes = VntQuote::with('detalles')
            ->when($this->search, function ($query) {
                $query->where('consecutive', 'like', '%' . $this->search . '%')
                    ->orWhere('customerId', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.tenant.quoter.components.quoter-mobile', [
            'quotes' => $quotes
        ]);
    }
}