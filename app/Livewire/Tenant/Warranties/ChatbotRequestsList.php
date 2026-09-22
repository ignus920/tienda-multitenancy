<?php

namespace App\Livewire\Tenant\Warranties;

use App\Models\Tenant\Sales\VntChatbotWarrantyRequest;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ChatbotRequestsExport;

class ChatbotRequestsList extends Component
{
    use WithPagination;

    public $search = '';
    public $startDate = '';
    public $endDate = '';
    public $statusFilter = 'all';

    public function boot()
    {
        abort_unless(
            \App\Helpers\PermissionHelper::isSuperAdmin()
            || \App\Helpers\PermissionHelper::userCan('Garantias Chatbot', 'show')
            || \App\Helpers\PermissionHelper::userCan('Garantias', 'show'),
            403
        );

        $this->ensureTenantConnection();
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

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }

    public function clearFilters()
    {
        $this->search = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->statusFilter = 'all';
        $this->resetPage();
    }

    public function processRequest($id)
    {
        return redirect()->route('tenant.warranties.create', ['id' => 'chatbot-' . $id]);
    }

    private function buildQuery()
    {
        $query = VntChatbotWarrantyRequest::query();

        // Filtro de Estado
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Filtro de Fechas (sobre created_at)
        if (!empty($this->startDate)) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if (!empty($this->endDate)) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        // Buscador Multi-palabra (Regla de negocio)
        if (!empty(trim($this->search))) {
            $words = explode(' ', trim($this->search));
            foreach ($words as $word) {
                $word = trim($word);
                if (empty($word)) continue;
                
                $query->where(function($q) use ($word) {
                    $q->where('company_name', 'like', "%{$word}%")
                      ->orWhere('reference_number', 'like', "%{$word}%")
                      ->orWhere('product_details', 'like', "%{$word}%")
                      ->orWhere('tracking_code', 'like', "%{$word}%");
                });
            }
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $requests = $this->buildQuery()->paginate(10);

        return view('livewire.tenant.warranties.chatbot-requests-list', [
            'requests' => $requests
        ])->layout('layouts.app');
    }

    public function exportExcel()
    {
        $data = $this->buildQuery()->get();
        return Excel::download(new ChatbotRequestsExport($data), 'Bandeja_Garantias_Web_' . date('Ymd_His') . '.xlsx');
    }

    public function exportPdf()
    {
        $data = $this->buildQuery()->get();
        return Excel::download(new ChatbotRequestsExport($data), 'Bandeja_Garantias_Web_' . date('Ymd_His') . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    }
}
