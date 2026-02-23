<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Imports\ImpLabels;
//Servicios
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImportLabels extends Component
{
    use WithPagination;

    public $label_id;
    public $name;
    public $asap;
    public $estimated_date;
    public $description;
    public $status;
    public $user_id;
    public $year;

    //Propiedades de la tabla
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'asc';
    public $showModal = false;

    protected $rules = [
        'name' => 'required'
    ];

    protected $messages = [
        'name.required' => 'El nombre es obligatorio',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
        $this->resetPage();
    }

    public function getLabelsProperty()
    {
        $this->ensureTenantConnection();
        $query = ImpLabels::where('status', 1);
        $results = $query->paginate($this->perPage);
        return $results;
    }

    public function cancel()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function create()
    {
        $this->showModal = true;
    }

    public function save()
    {
        $this->ensureTenantConnection();
        $this->resetValidation();
        $this->validate();
        try {
            $infoLabel = [
                'name' => $this->name,
                'description' => $this->description,
                'asap' => 0,
                'user_id' => Auth::id()
            ];

            if ($this->label_id) {
                $label = ImpLabels::findOrFail($this->label_id);
                $label->update($infoLabel);
                session()->flash('success', 'Registro actualizado exitosamente.');
            } else {
                ImpLabels::create($infoLabel);
                session()->flash('success', 'Registro realizado exitosamente.');
            }

            $this->resetForm();
            $this->showModal = false;
        } catch (\Exception $e) {
            Log::error('❌ Error al guardar información de importación: ' . $e->getMessage());
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
            return;
        }
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
    }

    public function edit($label_id)
    {
        $this->ensureTenantConnection();
        $label = ImpLabels::findOrFail($label_id);
        $this->label_id = $label->id;
        $this->name = $label->name;
        $this->description = $label->description;
        $this->showModal = true;
    }

    public function toggleLabelStatus($label_id)
    {
        $this->ensureTenantConnection();
        $label = ImpLabels::findOrFail($label_id);

        $newStatus = $label->status ? 0 : 1;
        $label->update([
            'status' => $newStatus,
        ]);

        session()->flash('success', 'Estado actualizado correctamente');
    }

    public function generateLabels()
    {
        $this->ensureTenantConnection();
        $this->resetValidation();

        if (empty($this->year)) {
            $this->addError('year', 'El año es obligatorio para generar etiquetas.');
            return;
        }

        if (!preg_match('/^\d{4}$/', $this->year)) {
            $this->addError('year', 'El año debe ser un formato de 4 dígitos (ej: 2026).');
            return;
        }

        try {
            DB::connection('tenant')->beginTransaction();

            $months = [
                'ENE',
                'FEB',
                'MAR',
                'ABR',
                'MAY',
                'JUN',
                'JUL',
                'AGO',
                'SEP',
                'OCT',
                'NOV',
                'DIC'
            ];

            $yearSuffix = substr($this->year, -2);

            foreach ($months as $month) {
                ImpLabels::create([
                    'name' => $month . $yearSuffix,
                    'description' => "Etiqueta generada para {$month} del {$this->year}",
                    'asap' => 0,
                    'status' => 1,
                    'user_id' => Auth::id()
                ]);
            }

            DB::connection('tenant')->commit();

            $generatedYear = $this->year;
            $this->year = '';
            session()->flash('success', "Se han generado las 12 etiquetas para el año {$generatedYear} correctamente.");
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('❌ Error al generar etiquetas masivas: ' . $e->getMessage());
            session()->flash('error', 'Error al generar etiquetas: ' . $e->getMessage());
        }
    }


    public function render()
    {
        $labels = $this->labels;
        return view('livewire.tenant.imports.import-labels', [
            'labels' => $labels
        ])->layout('layouts.app', ['header' => 'Gestión de Etiquetas']);
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
}
