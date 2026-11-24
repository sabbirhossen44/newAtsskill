<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/** Port of the old global file_upload() helper (old app/Helpers/helper.php). */
trait UploadsFiles
{
    protected function uploadFile(UploadedFile $file, string $path = 'uploads/custom-images/', string|array|null $oldFile = '', bool $optimize = false): string
    {
        $extension = $file->getClientOriginalExtension();
        $file_name = 'wsus-img'.date('-Y-m-d-h-i-s-').rand(999, 9999).'.'.$extension;
        $file_name = $path.$file_name;
        $file->move(public_path($path), $file_name);

        try {
            if ($oldFile && is_string($oldFile) && ! str($oldFile)->contains('uploads/website-images') && File::exists(public_path($oldFile))) {
                File::delete(public_path($oldFile));
            }
        } catch (\Exception $e) {
            Log::info($e->getMessage());
        }

        return $file_name;
    }
}
