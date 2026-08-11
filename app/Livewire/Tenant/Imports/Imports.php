<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Imports extends Component
{
    use WithPagination;

    public function render()
    {        
        return view('livewire.tenant.imports.home-import');
    }
}