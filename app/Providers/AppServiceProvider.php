<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\MakeLivewireModuleCommand::class,
            ]);
        }

        // Register Livewire components
        Livewire::component('auth.enable2-f-a', \App\Auth\Livewire\Enable2FA::class);

        // Event listeners for global auditing
        \Illuminate\Support\Facades\Event::listen('eloquent.created: *', function ($eventName, array $data) {
            $model = $data[0];
            if ($model->getConnectionName() !== 'tenant') return;
            if ($model instanceof \App\Models\Tenant\CnfModelLog || 
                $model instanceof \App\Models\Tenant\CnfAlegraLog || 
                $model instanceof \App\Models\Tenant\CnfLogAcceso) return;

            try {
                \App\Models\Tenant\CnfModelLog::create([
                    'model_type' => get_class($model),
                    'model_id' => $model->getKey(),
                    'action' => 'create',
                    'old_values' => null,
                    'new_values' => $model->getAttributes(),
                    'user_id' => auth()->id(),
                ]);
            } catch (\Exception $e) {
                \Log::error('Error recording audit log (created): ' . $e->getMessage());
            }
        });

        \Illuminate\Support\Facades\Event::listen('eloquent.updated: *', function ($eventName, array $data) {
            $model = $data[0];
            if ($model->getConnectionName() !== 'tenant') return;
            if ($model instanceof \App\Models\Tenant\CnfModelLog || 
                $model instanceof \App\Models\Tenant\CnfAlegraLog || 
                $model instanceof \App\Models\Tenant\CnfLogAcceso) return;

            $changes = $model->getChanges();
            if (empty($changes)) return;

            $old = [];
            $new = [];
            foreach ($changes as $key => $value) {
                if ($key === 'updated_at') continue;
                $old[$key] = $model->getOriginal($key);
                $new[$key] = $value;
            }

            if (empty($new)) return;

            try {
                \App\Models\Tenant\CnfModelLog::create([
                    'model_type' => get_class($model),
                    'model_id' => $model->getKey(),
                    'action' => 'update',
                    'old_values' => $old,
                    'new_values' => $new,
                    'user_id' => auth()->id(),
                ]);
            } catch (\Exception $e) {
                \Log::error('Error recording audit log (updated): ' . $e->getMessage());
            }
        });

        \Illuminate\Support\Facades\Event::listen('eloquent.deleted: *', function ($eventName, array $data) {
            $model = $data[0];
            if ($model->getConnectionName() !== 'tenant') return;
            if ($model instanceof \App\Models\Tenant\CnfModelLog || 
                $model instanceof \App\Models\Tenant\CnfAlegraLog || 
                $model instanceof \App\Models\Tenant\CnfLogAcceso) return;

            try {
                \App\Models\Tenant\CnfModelLog::create([
                    'model_type' => get_class($model),
                    'model_id' => $model->getKey(),
                    'action' => 'delete',
                    'old_values' => $model->getAttributes(),
                    'new_values' => null,
                    'user_id' => auth()->id(),
                ]);
            } catch (\Exception $e) {
                \Log::error('Error recording audit log (deleted): ' . $e->getMessage());
            }
        });
    }
}
