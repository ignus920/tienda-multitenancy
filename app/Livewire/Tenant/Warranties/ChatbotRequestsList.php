<?php

namespace App\Livewire\Tenant\Warranties;

use App\Models\Tenant\Sales\VntChatbotWarrantyRequest;
use Livewire\Component;
use Livewire\WithPagination;

class ChatbotRequestsList extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function processRequest($id)
    {
        // Al procesar, nos llevaremos este ID a la vista de crear garantía
        return redirect()->route('tenant.warranties.create', ['id' => 'chatbot-' . $id]);
    }

    public function render()
    {
        $requests = VntChatbotWarrantyRequest::where('company_name', 'like', '%' . $this->search . '%')
            ->orWhere('reference_number', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.tenant.warranties.chatbot-requests-list', [
            'requests' => $requests
        ])->layout('layouts.tenant');
    }
}
