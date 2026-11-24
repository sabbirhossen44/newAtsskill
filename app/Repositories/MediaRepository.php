<?php

namespace App\Repositories;

use App\Models\Media;
use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\Model;

class MediaRepository
{
    public static function model()
    {
        return Media::class;
    }

    public static function findById(int $id): ?Media
    {
        return static::model()::query()->find($id);
    }

    public static function storeByRequest(UploadedFile $file, string $path, ?string $type = null, ?string $folder = null, ?string $description = null): Media
    {
        [$upload, $optimizedPath] = ImageService::optimize($file);

        try {
            $storedPath = Storage::disk('public')->putFile($path, $optimizedPath ? new \Illuminate\Http\File($optimizedPath) : $upload);
        } finally {
            if ($optimizedPath && file_exists($optimizedPath)) {
                unlink($optimizedPath);
            }
        }

        $extension = strtolower($file->getClientOriginalExtension());

        return static::model()::query()->create([
            'name' => $file->getClientOriginalName(),
            'src' => $storedPath,
            'type' => $type ?? (in_array($extension, ImageService::SUPPORTED_EXTENSIONS, true) ? 'image' : $extension),
            'extension' => $extension,
        ]);
    }

    public static function updateByRequest(UploadedFile $file, string $path, ?string $type, Media $media, ?string $folder = null): Media
    {
        $fresh = static::storeByRequest($file, $path, $type, $folder);

        static::deleteByMedia($media);

        return $fresh;
    }

    public static function updateOrCreateByRequest(UploadedFile $file, string $path, ?string $type, ?Media $media, ?string $folder = null): Media
    {
        if ($media) {
            return static::updateByRequest($file, $path, $type, $media, $folder);
        }

        return static::storeByRequest($file, $path, $type, $folder);
    }

    public static function deleteByMedia(Media $media): bool
    {
        if ($media->src) {
            Storage::disk('public')->delete($media->src);
        }

        return (bool) $media->delete();
    }

    public static function storeByFromUrlRequest(string $url, string $path, ?string $description = null): ?Media
    {
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            return null;
        }

        $contents = @file_get_contents($url);

        if ($contents === false) {
            return null;
        }

        $name = basename(parse_url($url, PHP_URL_PATH) ?: 'remote-file');
        $storedPath = Storage::disk('public')->put($path.'/'.$name, $contents);

        if (! $storedPath) {
            return null;
        }

        return static::model()::query()->create([
            'name' => $name,
            'src' => $storedPath,
            'type' => 'image',
            'extension' => strtolower(pathinfo($name, PATHINFO_EXTENSION)),
        ]);
    }

    public static function optimizeExisting(Media $media, bool $force = false): bool
    {
        return false;
    }

    /**
     * Sync a parent model's pivot media rows (course_media, blog_media, ...).
     * $medias: [['file' => UploadedFile, 'is_primary' => bool], ...]
     * $existingMediaIds: pivot media_ids to keep; others are removed.
     */
    public static function syncForParent(Model $parent, array $medias, string $pivotClass, string $parentKey, ?array $existingMediaIds = null): void
    {
        $order = (int) $pivotClass::query()->where($parentKey, $parent->id)->max('order');

        foreach ($medias as $item) {
            $file = $item['file'] ?? null;

            if (! $file instanceof UploadedFile) {
                continue;
            }

            $media = static::storeByRequest($file, strtolower(class_basename($parent)), 'image');

            $pivotClass::query()->create([
                $parentKey => $parent->id,
                'media_id' => $media->id,
                'is_primary' => (bool) ($item['is_primary'] ?? false),
                'order' => ++$order,
            ]);
        }

        if (is_array($existingMediaIds)) {
            $pivotClass::query()
                ->where($parentKey, $parent->id)
                ->whereNotIn('media_id', $existingMediaIds)
                ->delete();
        }
    }
}
