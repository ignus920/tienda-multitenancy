<?php

namespace App\Livewire\Tenant\VntCompany;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Customer\VntCompanyRoute;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Livewire\Attributes\On;

class MoveDistrictModal extends Component
{
    use WithPagination;

    public $showModal = false;
    public $search = '';
    public $perPage = 10;

    // Form properties
    public $districtId = '';
    public $warehouseCityId = null;
    public $sourceRouteId = '';
    public $targetRouteId = '';
    public $selectedCompanies = [];
    public $selectAll = false;
    public $availableDistricts = [];

    protected $listeners = [
        'source-route-changed' => 'updateSourceRoute',
        'target-route-changed' => 'updateTargetRoute',
        'city-changed' => 'updateCity',
        'district-changed' => 'updateDistrict'
    ];

    protected function rules()
    {
        return [
            'districtId' => 'nullable|integer',
            'targetRouteId' => 'required|exists:vnt_routes,id',
        ];
    }

    protected function messages()
    {
        return [
            'targetRouteId.required' => 'Debe seleccionar una ruta de destino.',
            'targetRouteId.exists' => 'La ruta de destino seleccionada no existe.',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedCompanies = $this->getFilteredCompanies()->pluck('id')->toArray();
        } else {
            $this->selectedCompanies = [];
        }
    }

    public function getFilteredCompanies()
    {
        $this->ensureTenantConnection();
        if (empty($this->districtId)) {
            return collect([]);
        }

        $query = VntCompanyRoute::query()
            ->with(['company', 'company.mainWarehouse'])
            ->whereHas('company.mainWarehouse', function ($query) {
                $query->where('district', $this->districtId);
            });

        // NO filtrar por sourceRouteId aquí tampoco
        // Este método se usa para el selectAll

        $query->when($this->search, function ($query) {
            $query->whereHas('company', function ($q) {
                $q->where('businessName', 'like', '%' . $this->search . '%')
                    ->orWhere('firstName', 'like', '%' . $this->search . '%')
                    ->orWhere('lastName', 'like', '%' . $this->search . '%')
                    ->orWhere('identification', 'like', '%' . $this->search . '%');
            });
        });

        return $query->get();
    }

    public function getItemsProperty()
    {
        $this->ensureTenantConnection();
        try {
            // Si no hay distrito seleccionado, no mostrar nada
            if (empty($this->districtId)) {
                return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
            }

            // Mostrar todos los clientes del barrio seleccionado
            $query = VntCompanyRoute::query()
                ->with(['company', 'company.mainWarehouse', 'route.salesman'])
                ->whereHas('company.mainWarehouse', function ($query) {
                    $query->where('district', $this->districtId);
                });

            // NO filtrar por sourceRouteId - la tabla siempre muestra todos los clientes del distrito
            // El sourceRouteId solo se usa para el botón de intercambio

            // Aplicar búsqueda si existe
            $query->when($this->search, function ($query) {
                $query->whereHas('company', function ($q) {
                    $q->where('businessName', 'like', '%' . $this->search . '%')
                        ->orWhere('firstName', 'like', '%' . $this->search . '%')
                        ->orWhere('lastName', 'like', '%' . $this->search . '%')
                        ->orWhere('identification', 'like', '%' . $this->search . '%');
                });
            });
            return $query->orderBy('route_id')->orderBy('sales_order')->paginate($this->perPage);
        } catch (\Exception $e) {
            Log::error('Error getting companies by district: ' . $e->getMessage());
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }
    }

    public function mount()
    {
        //$this->loadAvailableDistricts();
    }

    public function loadAvailableDistricts()
    {
        $this->ensureTenantConnection();
        try {
            $this->availableDistricts = DB::table('vnt_warehouses')
                ->select('district')
                ->whereNotNull('district')
                ->where('district', '!=', '')
                ->where('district', '!=', '000')
                ->distinct()
                ->orderBy('district')
                ->pluck('district')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading districts: ' . $e->getMessage());
            $this->availableDistricts = [];
        }
    }

    public function render()
    {
        return view('livewire.tenant.vnt-company.components.move-district-modal', [
            'items' => $this->items,
        ]);
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('move-district-modal-closed');
    }

    public function updatedDistrictId()
    {
        // Reset selections when district changes
        $this->selectedCompanies = [];
        $this->selectAll = false;
        $this->sourceRouteId = '';
        $this->targetRouteId = '';
        $this->resetPage();
    }

    public function swapRoutes()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'districtId' => 'nullable|integer',
            'sourceRouteId' => 'required',
            'targetRouteId' => 'required',
        ]);

        if ($this->sourceRouteId == $this->targetRouteId) {
            session()->flash('error', 'Las rutas de origen y destino deben ser diferentes.');
            return;
        }

        try {
            DB::beginTransaction();

            // Obtener todos los clientes de la ruta origen en el distrito
            $sourceCompanies = VntCompanyRoute::query()
                ->whereHas('company.mainWarehouse', function ($query) {
                    $query->where('district', $this->districtId);
                })
                ->where('route_id', $this->sourceRouteId)
                ->get();

            // Obtener todos los clientes de la ruta destino en el distrito
            $targetCompanies = VntCompanyRoute::query()
                ->whereHas('company.mainWarehouse', function ($query) {
                    $query->where('district', $this->districtId);
                })
                ->where('route_id', $this->targetRouteId)
                ->get();

            // Intercambiar: Mover clientes de origen a destino
            foreach ($sourceCompanies as $company) {
                $company->update(['route_id' => $this->targetRouteId]);
            }

            // Intercambiar: Mover clientes de destino a origen
            foreach ($targetCompanies as $company) {
                $company->update(['route_id' => $this->sourceRouteId]);
            }

            DB::commit();

            $totalSwapped = $sourceCompanies->count() + $targetCompanies->count();
            session()->flash('message', "Se intercambiaron {$totalSwapped} cliente(s) entre las rutas exitosamente.");

            $this->sourceRouteId = '';
            $this->targetRouteId = '';
            $this->selectedCompanies = [];
            $this->selectAll = false;
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error swapping routes: ' . $e->getMessage());
            session()->flash('error', 'Error al intercambiar rutas: ' . $e->getMessage());
        }
    }

    public function moveCompanies()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'targetRouteId' => 'required|exists:vnt_routes,id',
        ]);

        if (empty($this->selectedCompanies)) {
            session()->flash('error', 'Debe seleccionar al menos un cliente para mover.');
            return;
        }

        try {
            DB::beginTransaction();

            $movedCount = 0;
            foreach ($this->selectedCompanies as $companyRouteId) {
                $companyRoute = VntCompanyRoute::find($companyRouteId);

                if ($companyRoute) {
                    // Obtener el último sales_order de la ruta destino
                    $lastOrder = VntCompanyRoute::where('route_id', $this->targetRouteId)
                        ->orderBy('sales_order', 'desc')
                        ->first();

                    $newSalesOrder = $lastOrder ? ($lastOrder->sales_order + 1) : 1;

                    $companyRoute->update([
                        'route_id' => $this->targetRouteId,
                        'sales_order' => $newSalesOrder,
                    ]);

                    $movedCount++;
                }
            }

            DB::commit();

            session()->flash('message', "Se movieron {$movedCount} cliente(s) exitosamente.");
            $this->selectedCompanies = [];
            $this->selectAll = false;
            $this->targetRouteId = '';
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error moving companies: ' . $e->getMessage());
            session()->flash('error', 'Error al mover clientes: ' . $e->getMessage());
        }
    }

    public function updateSourceRoute($routeId)
    {
        $this->sourceRouteId = $routeId;
        // NO resetear selecciones ni página
        // Solo actualizar el valor para que aparezca el botón de intercambio
    }

    public function updateTargetRoute($routeId)
    {
        $this->targetRouteId = $routeId;
        // Solo actualizar el valor para que aparezca el botón de intercambio
    }

    #[On('district-changed')]
    public function updateDistrict($districtId)
    {
        $this->districtId = $districtId ? (int)$districtId : null;
        \Illuminate\Support\Facades\Log::info('MoveDistrictModal: district updated', [
            'districtId' => $this->districtId
        ]);
    }

    #[On('city-changed')]
    public function updateCity($cityId)
    {
        \Illuminate\Support\Facades\Log::info('CitySelect: city-changed received', [
            'cityId' => $cityId,
        ]);
        $this->warehouseCityId = $cityId ? (int)$cityId : null;
        $this->districtId = null;
    }

    private function resetForm()
    {
        $this->districtId = '';
        $this->sourceRouteId = '';
        $this->targetRouteId = '';
        $this->selectedCompanies = [];
        $this->selectAll = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    private function ensureTenantConnection(): void
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            throw new \Exception('No tenant selected');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            throw new \Exception('Invalid tenant');
        }

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}
