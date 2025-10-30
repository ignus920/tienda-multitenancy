<?php

use App\Http\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        try {
            $this->form->authenticate();

            // Si llegamos aquí, no hay 2FA o ya fue validado
            Session::regenerate();

            // Verificar si ya hay 2FA pendiente
            if (Session::has('2fa_user_id')) {
                $this->redirect(route('verify.2fa'), navigate: true);
                return;
            }

            // Redirigir a selección de tenant
            $this->redirect(route('tenant.select'), navigate: true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Si hay 2FA habilitado, redirigir a verificación
            if (Session::has('2fa_user_id')) {
                $this->redirect(route('verify.2fa'), navigate: true);
                return;
            }

            throw $e;
        }
    }
}; ?>

<div class="flex min-h-screen flex-col justify-center px-6 py-12 lg:px-8 lg:py-20">
    <!-- Header -->
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <div class="mx-auto h-10 w-10 flex items-center justify-center bg-indigo-600 rounded-lg">
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-1.25 0V3.75a.75.75 0 00-.75-.75H14.25a.75.75 0 00-.75.75V4.5" />
            </svg>
        </div>
        <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900 dark:text-white">
            Iniciar sesión en tu cuenta
        </h2>
    </div>

    <!-- Form -->
    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form wire:submit="login" class="space-y-6">
            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">
                    Correo electrónico
                </label>
                <div class="mt-2">
                    <input
                        wire:model="form.email"
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        autocomplete="email"
                        class="block w-full rounded-md bg-white dark:bg-white/5 px-3 py-1.5 text-base text-gray-900 dark:text-white outline-1 -outline-offset-1 outline-gray-300 dark:outline-white/10 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                        placeholder="tu@email.com"
                    />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                </div>
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">
                        Contraseña
                    </label>
                    @if (Route::has('password.request'))
                        <div class="text-sm">
                            <a href="{{ route('password.request') }}" wire:navigate class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                    @endif
                </div>
                <div class="mt-2">
                    <input
                        wire:model="form.password"
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="block w-full rounded-md bg-white dark:bg-white/5 px-3 py-1.5 text-base text-gray-900 dark:text-white outline-1 -outline-offset-1 outline-gray-300 dark:outline-white/10 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                        placeholder="••••••••"
                    />
                    <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <input
                    wire:model="form.remember"
                    id="remember"
                    type="checkbox"
                    name="remember"
                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 dark:border-gray-600 dark:bg-gray-700 dark:focus:ring-indigo-500"
                >
                <label for="remember" class="ml-3 block text-sm/6 text-gray-900 dark:text-gray-100">
                    Recordarme
                </label>
            </div>

            <!-- Submit Button -->
            <div>
                <button
                    type="submit"
                    class="flex w-full justify-center items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 min-h-[40px]"
                    wire:loading.attr="disabled"
                >
                    <div class="flex items-center justify-center">
                        <svg wire:loading class="animate-spin h-4 w-4 text-white mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove>Iniciar sesión</span>
                        <span wire:loading>Iniciando sesión...</span>
                    </div>
                </button>
            </div>
        </form>

        <!-- Register Link -->
        @if (Route::has('register'))
            <p class="mt-10 text-center text-sm/6 text-gray-500 dark:text-gray-400">
                ¿No tienes una cuenta?
                <a href="{{ url('/') }}" wire:navigate class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                    Ir a página principal
                </a>
            </p>
        @endif
    </div>
</div>
