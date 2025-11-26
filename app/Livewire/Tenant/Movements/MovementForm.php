<?php

namespace App\Livewire\Tenant\Movements;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Auth\User;
use App\Models\Auth\UserTenant;
use App\Models\Central\UsrProfile;
use App\Models\Central\VntWarehouse;
use App\Models\Central\VntContact;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\UserExportService;
use App\Traits\HasCompanyConfiguration;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;

class MovementForm extends Component
{
    // use WithPagination, HasCompanyConfiguration;
    

   public $foo;
 

    public function render()
    {
         Log::info('MovementForm: render method called');
         return view('livewire.tenant.movements.components.movement-form', [
             'movements' => [],
        ]);
    }
    public function boot()
    {
        Log::info('MovementForm: boot method called');
    }
 
    public function booted()
    {
        Log::info('MovementForm: booted method called');
    }
 
    public function mount()
    {
        Log::info('MovementForm: mount method called');
    }
 
    public function hydrateFoo($value)
    {
        Log::info('MovementForm: hydrateFoo method called', ['value' => $value]);
    }
 
    public function dehydrateFoo($value)
    {
        Log::info('MovementForm: dehydrateFoo method called', ['value' => $value]);
    }
 
    public function hydrate()
    {
        Log::info('MovementForm: hydrate method called');
    }
 
    public function dehydrate()
    {
        Log::info('MovementForm: dehydrate method called');
    }
 
    public function updating($name, $value)
    {
        Log::info('MovementForm: updating method called', [
            'name' => $name,
            'value' => $value
        ]);
    }
 
    public function updated($name, $value)
    {
        Log::info('MovementForm: updated method called', [
            'name' => $name,
            'value' => $value
        ]);
    }
 
    public function updatingFoo($value)
    {
        Log::info('MovementForm: updatingFoo method called', ['value' => $value]);
    }
 
    public function updatedFoo($value)
    {
        Log::info('MovementForm: updatedFoo method called', ['value' => $value]);
    }
 
    public function updatingFooBar($value)
    {
        Log::info('MovementForm: updatingFooBar method called', ['value' => $value]);
    }
 
    public function updatedFooBar($value)
    {
        Log::info('MovementForm: updatedFooBar method called', ['value' => $value]);
    }
}
