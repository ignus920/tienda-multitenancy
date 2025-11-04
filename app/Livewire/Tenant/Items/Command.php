<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use App\Models\Tenant\Items\Command  as CommandModel;

class Command extends Component
{
    public $commandId = '';
    public $name = 'commandId';
    public $placeholder = 'Seleccione una comanda';
    public $label = 'Comanda';
    public $required = true;
    public $showLabel = true;
    public $class = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500';

    public function mount($commandId = '', $name = 'commandId', $placeholder = 'Seleccione una comanda', $label = 'Comanda', $required = true, $showLabel = true, $class = null)
    {
        $this->commandId = $commandId;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->label = $label;
        $this->required = $required;
        $this->showLabel = $showLabel;
        if ($class) {
            $this->class = $class;
        }
    }

    public function updatedCommandId(){
        $this->dispatch('command-changed', $this->commandId);
    }

    public function getCommandsProperty()
    {
        // Cargar todas las comandas desde la base de datos
        return CommandModel::where('status', 1)->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.tenant.items.command', [
            'commands' => $this->commands,
            'showLabel' => $this->showLabel
        ]);
    }
}
