<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

/**
 * TinyMCE / forum-image handling for the learning-player Q&A.
 * deleteEmbeddedImages() is a verbatim port of the old
 * extractAndFilterImageSrc() helper, which the rebuild had not ported
 * (its call sites would fatal on an undefined function).
 */
class TinymceImageService
{
    public static function store(UploadedFile $file): string
    {
        return FileUploadService::upload($file, 'uploads/forum-images/');
    }

    public static function deleteByPath(?string $filePath): bool
    {
        $image_path = preg_replace('/^.*\/(uploads\/.*)$/', '$1', $filePath ?? '');
        $fullPath = public_path($image_path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);

            return true;
        }

        return false;
    }

    public static function deleteEmbeddedImages(?string $html): void
    {
        preg_match_all('/<img[^>]+src="([^">]+)"/i', $html ?? '', $matches);

        foreach (array_filter(array_map(function ($src) {
            $path = preg_replace('/^.*\/(uploads\/.*)$/', '$1', $src);

            return preg_match('/^uploads\/forum-images\/[^\/]+\.[a-zA-Z]{3,4}$/', $path) ? $path : null;
        }, $matches[1])) as $image) {
            $fullPath = public_path($image);

            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }
    }
}
