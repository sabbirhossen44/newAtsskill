<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class CloudStorageService
{
    public static function store(UploadedFile $file, string $disk): ?string
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = strtolower(config('app.name')).date('-Y-m-d-H-i-s-').rand(999, 9999).'.'.$extension;

        $path = $file->storeAs('uploads', $fileName, $disk);

        return $path ?: null;
    }
}
