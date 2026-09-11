<?php

namespace App\Livewire\Tenant\Instructivos;

use App\Helpers\PermissionHelper;
use App\Models\Tenant\Instructivos\Instructivo;
use App\Models\Tenant\Instructivos\InstructivoEntry;
use Illuminate\Support\Facades\Auth;
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

    public function boot()
    {
        abort_unless(
            PermissionHelper::isSuperAdmin() || PermissionHelper::userCan('Instructivos', 'show'),
            403
        );

        $this->canManage = PermissionHelper::isSuperAdmin() || PermissionHelper::userCan('Instructivos', 'edit');
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
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.tenant.instructivos.instructivo-show', [
            'instructivo' => $instructivo,
            'entries' => $entries,
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
        $this->showEntryModal = true;
    }

    public function saveEntry()
    {
        abort_unless($this->canManage, 403);

        $this->validate([
            'entryTitle' => 'required|string|max:150',
            'entryBody' => 'required|string',
            'tempFiles.*' => 'nullable|file|max:10240',
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

        foreach ($this->tempFiles as $file) {
            $path = $file->store('instructivos', 'public');
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

    public function removeTempFile(int $index)
    {
        unset($this->tempFiles[$index]);
        $this->tempFiles = array_values($this->tempFiles);
    }

    private function resetEntryForm()
    {
        $this->editingEntryId = null;
        $this->entryTitle = '';
        $this->entryBody = '';
        $this->tempFiles = [];
        $this->resetErrorBag();
    }
}
