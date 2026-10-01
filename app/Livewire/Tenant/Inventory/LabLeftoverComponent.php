<?php

namespace App\Livewire\Tenant\Inventory;

use Livewire\Component;
use App\Models\Tenant\Inventory\LabLeftover;

class LabLeftoverComponent extends Component
{
    public function render()
    {
        $leftovers = LabLeftover::with('item.dimensions')->get();
        return view('livewire.tenant.inventory.lab-leftover-component', [
            'leftovers' => $leftovers
        ])->layout('layouts.app');
    }
}
