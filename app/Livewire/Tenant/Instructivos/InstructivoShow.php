<?php

namespace App\Livewire\Tenant\Instructivos;

use App\Helpers\PermissionHelper;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Instructivos\Instructivo;
use App\Models\Tenant\Instructivos\InstructivoAttachment;
use App\Models\Tenant\Instructivos\InstructivoEntry;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class InstructivoShow extends Component
{
    use WithFileUploads;

    public int $instructivoId;

    public bool $canManage = false;

    public bool $showEntryModal = false;
    public ?int $editingEntryId = null;
    public string $entryTitle = '';
    public string $entryBody = '';
    public $tempFiles = [];
    public array $stagedFiles = [];

    public function boot()
    {
        $this->ensureTenantConnection();

        abort_unless(
            PermissionHelper::isSuperAdmin() || PermissionHelper::userCan('Instructivos', 'show'),
            403
        );

        $this->canManage = PermissionHelper::isSuperAdmin() || PermissionHelper::userCan('Instructivos', 'edit');
    }

    // El <input multiple> reemplaza su selección completa cada vez que se abre
    // el diálogo (así es el navegador, no algo que controlemos). Por eso cada
    // tanda que llega a $tempFiles se va sumando a $stagedFiles, en vez de
    // usar $tempFiles directo, para no perder lo ya elegido de otra carpeta.
    public function updatedTempFiles()
    {
        foreach ($this->tempFiles as $file) {
            $this->stagedFiles[] = $file;
        }
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);
    }

    public function mount(int $instructivo)
    {
        $exists = Instructivo::where('status', 1)->where('id', $instructivo)->exists();
        abort_unless($exists, 404);

        $this->instructivoId = $instructivo;
    }

    public function render()
    {
        $instructivo = Instructivo::with('department')->findOrFail($this->instructivoId);

        $entries = InstructivoEntry::where('instructivo_id', $this->instructivoId)
            ->where('status', 1)
            ->with(['attachments', 'author'])
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->get();

        $existingAttachments = $this->editingEntryId
            ? InstructivoAttachment::where('entry_id', $this->editingEntryId)->get()
            : collect();

        return view('livewire.tenant.instructivos.instructivo-show', [
            'instructivo' => $instructivo,
            'entries' => $entries,
            'existingAttachments' => $existingAttachments,
        ])->layout('layouts.app', ['header' => $instructivo->title]);
    }

    public function openNewEntry()
    {
        abort_unless($this->canManage, 403);
        $this->resetEntryForm();
        $this->showEntryModal = true;
    }

    public function openEditEntry(int $id)
    {
        abort_unless($this->canManage, 403);
        $entry = InstructivoEntry::where('instructivo_id', $this->instructivoId)->findOrFail($id);
        $this->editingEntryId = $entry->id;
        $this->entryTitle = $entry->title;
        $this->entryBody = $entry->body;
        $this->tempFiles = [];
        $this->stagedFiles = [];
        $this->showEntryModal = true;
    }

    public function saveEntry()
    {
        abort_unless($this->canManage, 403);

        $this->validate([
            'entryTitle' => 'required|string|max:150',
            'entryBody' => 'required|string',
            'stagedFiles.*' => 'nullable|file|max:10240|mimes:png,jpg,jpeg,webp,pdf,xls,xlsx',
        ]);

        if ($this->editingEntryId) {
            $entry = InstructivoEntry::where('instructivo_id', $this->instructivoId)->findOrFail($this->editingEntryId);
            $entry->update([
                'title' => $this->entryTitle,
                'body' => $this->entryBody,
                'updated_by' => Auth::id(),
            ]);
        } else {
            $entry = InstructivoEntry::create([
                'instructivo_id' => $this->instructivoId,
                'title' => $this->entryTitle,
                'body' => $this->entryBody,
                'status' => 1,
                'created_by' => Auth::id(),
            ]);
        }

        foreach ($this->stagedFiles as $file) {
            $path = $this->storeKeepingOriginalName($file, 'instructivos');
            $entry->attachments()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
                'created_at' => now(),
            ]);
        }

        $this->showEntryModal = false;
        $this->resetEntryForm();
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Entrada guardada.']);
    }

    public function deactivateEntry(int $id)
    {
        abort_unless($this->canManage, 403);
        InstructivoEntry::where('instructivo_id', $this->instructivoId)->where('id', $id)->update(['status' => 0]);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Entrada desactivada.']);
    }

    public function moveEntryUp(int $id)
    {
        abort_unless($this->canManage, 403);
        $this->swapEntryOrder($id, -1);
    }

    public function moveEntryDown(int $id)
    {
        abort_unless($this->canManage, 403);
        $this->swapEntryOrder($id, 1);
    }

    /**
     * Intercambia el orden de la entrada $id con la que está inmediatamente
     * antes (-1) o después (+1) en el orden visible actual. Si ambas comparten
     * el mismo valor de "order" (aún no se ha reordenado nada, todas en 0),
     * primero se les asignan valores explícitos según su posición actual.
     */
    private function swapEntryOrder(int $id, int $direction)
    {
        $entries = InstructivoEntry::where('instructivo_id', $this->instructivoId)
            ->where('status', 1)
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->get(['id', 'order']);

        $position = $entries->search(fn ($entry) => $entry->id === $id);
        if ($position === false) {
            return;
        }

        $swapWith = $position + $direction;
        if ($swapWith < 0 || $swapWith >= $entries->count()) {
            return;
        }

        $a = $entries[$position];
        $b = $entries[$swapWith];

        if ($a->order === $b->order) {
            $a->order = $position;
            $b->order = $swapWith;
        }

        [$a->order, $b->order] = [$b->order, $a->order];

        $a->save();
        $b->save();
    }

    public function removeTempFile(int $index)
    {
        unset($this->stagedFiles[$index]);
        $this->stagedFiles = array_values($this->stagedFiles);
    }

    public function deleteExistingAttachment(int $attachmentId)
    {
        abort_unless($this->canManage, 403);

        $attachment = InstructivoAttachment::where('entry_id', $this->editingEntryId)->findOrFail($attachmentId);
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Archivo eliminado.']);
    }

    private function storeKeepingOriginalName($uploadedFile, string $directory): string
    {
        $originalName = $uploadedFile->getClientOriginalName();
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);

        $baseName = trim(preg_replace('/[^\pL\pN _\-\.]+/u', '_', $baseName));
        if ($baseName === '') {
            $baseName = 'archivo';
        }

        $disk = Storage::disk('public');
        $fileName = $extension ? "{$baseName}.{$extension}" : $baseName;
        $counter = 1;
        while ($disk->exists("{$directory}/{$fileName}")) {
            $fileName = $extension ? "{$baseName} ({$counter}).{$extension}" : "{$baseName} ({$counter})";
            $counter++;
        }

        return $uploadedFile->storeAs($directory, $fileName, 'public');
    }

    private function resetEntryForm()
    {
        $this->editingEntryId = null;
        $this->entryTitle = '';
        $this->entryBody = '';
        $this->tempFiles = [];
        $this->stagedFiles = [];
        $this->resetErrorBag();
    }
}
