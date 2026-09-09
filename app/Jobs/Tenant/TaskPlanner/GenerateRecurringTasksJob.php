<?php

namespace App\Jobs\Tenant\TaskPlanner;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Auth\Tenant;
use App\Models\Tenant\TaskPlanner\Task;
use App\Models\Tenant\TaskPlanner\TaskAssignment;
use App\Models\Tenant\TaskPlanner\TaskHistory;
use App\Models\Tenant\TaskPlanner\RecurringTask;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

/**
 * Genera las tareas de las plantillas recurrentes (tsk_recurring_tasks) que
 * ya vencieron su next_run_at. Se ejecuta una vez al día por tenant.
 */
class GenerateRecurringTasksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Tenant $tenant) {}

    public function handle(): void
    {
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($this->tenant);

        if (!tenancy()->initialized) {
            tenancy()->initialize($this->tenant);
        }

        // El módulo puede no estar instalado en este tenant.
        if (!Schema::connection('tenant')->hasTable('tsk_recurring_tasks')) {
            return;
        }

        $due = RecurringTask::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('next_run_at')->orWhere('next_run_at', '<=', now());
            })
            ->with(['baseTask.assignments', 'baseTask.checklists', 'baseTask.materials'])
            ->get();

        foreach ($due as $rt) {
            try {
                if (!$rt->baseTask) {
                    $rt->update(['is_active' => false]);
                    continue;
                }

                $this->spawn($rt);

                $next = RecurringTask::nextRunFrom($rt->cron_expression, now());
                if ($next->lte(now())) {
                    $next = now()->copy()->addDay();
                }

                $rt->update(['last_run_at' => now(), 'next_run_at' => $next]);
            } catch (\Throwable $e) {
                Log::error("[tsk-recurring] Tenant {$this->tenant->id} plantilla {$rt->id}: " . $e->getMessage());
            }
        }
    }

    private function spawn(RecurringTask $rt): void
    {
        $base = $rt->baseTask;

        $new = Task::create([
            'title' => $base->title,
            'description' => $base->description,
            'department_id' => $base->department_id,
            'priority' => $base->priority,
            'status' => 'sin_programar',
            'estimated_minutes' => $base->estimated_minutes,
            'deadline_at' => Carbon::now()->addDays(3)->setTime(17, 0),
            'suggested_date' => Carbon::now()->addDay()->toDateString(),
            'location_type' => $base->location_type,
            'location' => $base->location,
            'travel_minutes_before' => $base->travel_minutes_before,
            'travel_minutes_after' => $base->travel_minutes_after,
            'origin_type' => $base->origin_type,
            'origin_project_id' => $base->origin_project_id,
            'created_by' => $base->created_by,
        ]);

        foreach ($base->assignments as $a) {
            TaskAssignment::create(['task_id' => $new->id, 'user_id' => $a->user_id]);
        }

        foreach ($base->checklists as $c) {
            $new->checklists()->create([
                'description' => $c->description,
                'is_required' => $c->is_required,
                'is_completed' => false,
                'order' => $c->order,
            ]);
        }

        foreach ($base->materials as $m) {
            $new->materials()->create([
                'item_id' => $m->item_id,
                'name' => $m->name,
                'estimated_quantity' => $m->estimated_quantity,
            ]);
        }

        TaskHistory::log($new->id, null, 'generada_por_recurrencia', null, 'Desde plantilla recurrente #' . $rt->id);
    }
}
