<?php

namespace App\Livewire\Tenant\Users;

use App\Models\Auth\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ChangePasswordModal extends Component
{
    public $showModal = false;
    public $userId;
    public $user = null;
    public $newPassword = '';
    public $confirmPassword = '';

    protected $rules = [
        'newPassword' => 'required|min:8',
        'confirmPassword' => 'required|same:newPassword',
    ];

    protected $messages = [
        'newPassword.required' => 'La nueva contraseña es requerida.',
        'newPassword.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'confirmPassword.required' => 'La confirmación de contraseña es requerida.',
        'confirmPassword.same' => 'Las contraseñas no coinciden.',
    ];

    public function mount()
    {
        if ($this->userId) {
            $this->user = User::find($this->userId);
        }
    }

    public function openModal()
    {
        $this->user = User::findOrFail($this->userId);
        $this->showModal = true;
        $this->resetForm();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->newPassword = '';
        $this->confirmPassword = '';
        $this->resetValidation();
    }

    public function changePassword()
    {
        $this->validate();

        try {
            $this->user->update([
                'password' => Hash::make($this->newPassword)
            ]);

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Contraseña actualizada exitosamente para ' . $this->user->name
            ]);

            $this->closeModal();

        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al cambiar la contraseña: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.tenant.users.components.change-password-modal');
    }
}