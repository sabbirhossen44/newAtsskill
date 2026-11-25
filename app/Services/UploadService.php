<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Plain public-path file uploads (uploads/custom-images/,
 * uploads/team-members/, ...), extracted from the old
 * UploadsFiles controller concern so controllers and repositories
 * share one implementation. Behaviour is identical to the old
 * trait: wsus-img naming, old-file deletion (except files under
 * uploads/website-images), silent logging on delete failure.
 */
class UploadService
{
    public static function store(UploadedFile $file, string $path = 'uploads/custom-images/', ?string $oldFile = null, ?string $fileName = null): string
    {
        $extension = $file->getClientOriginalExtension();
        $fileName ??= 'wsus-img'.date('-Y-m-d-h-i-s-').rand(999, 9999).'.'.$extension;
        $fileName = $path.$fileName;

        $file->move(public_path($path), $fileName);

        try {
            if ($oldFile && is_string($oldFile) && ! str($oldFile)->contains('uploads/website-images') && File::exists(public_path($oldFile))) {
                File::delete(public_path($oldFile));
            }
        } catch (\Exception $e) {
            Log::info($e->getMessage());
        }

        return $fileName;
    }

    public static function delete(?string $path): void
    {
        if ($path && File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}
