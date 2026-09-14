<?php

namespace App\Http\Controllers\Tenant\Instructivos;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Instructivos\InstructivoAttachment;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function show(int $attachment)
    {
        $att = InstructivoAttachment::findOrFail($attachment);

        abort_unless(
            PermissionHelper::isSuperAdmin() || PermissionHelper::userCan('Instructivos', 'show'),
            403
        );

        abort_unless(Storage::disk('public')->exists($att->file_path), 404);

        return Storage::disk('public')->response(
            $att->file_path,
            $att->file_name,
            ['Cache-Control' => 'private, max-age=3600'],
            'inline'
        );
    }
}
