<?php

namespace App\Repositories;

use App\Models\Media;
use Arafat\LaravelRepository\Repository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaRepository extends Repository
{
    public static function model()
    {
        return Media::class;
    }

    /**
     * Store a media file in the database and file system.
     *
     * @param  UploadedFile  $file  The file to store.
     * @param  string  $path  The path to store the file in.
     * @param  string|null  $type  The type of media to store. If null, the type will be automatically determined.
     * @return Media The stored media.
     */
    public static function storeByRequest(UploadedFile $file, string $path, ?string $type = null): Media
    {
        $url = Storage::disk('public')->put('/'.trim($path, '/'), $file);

        if (! $type) {
            $extension = strtolower($file->getClientOriginalExtension());
            $type = in_array($extension, ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp']) ? 'image' : $extension;
        }

        return self::create([
            'type' => $type,
            'src' => $url,
            'name' => $file->getClientOriginalName(),
            'extension' => strtolower($file->getClientOriginalExtension()),
        ]);
    }

    /**
     * Replace the file of an existing media record.
     *
     * @param  UploadedFile  $file  The file to store.
     * @param  string  $path  The path to store the file in.
     * @param  string|null  $type  The type of media to store. If null, the type will be automatically determined.
     * @param  Media  $media  The existing media record to update.
     * @return Media The updated media record.
     */
    public static function updateByRequest(UploadedFile $file, string $path, ?string $type = null, Media $media): Media
    {
        $url = Storage::disk('public')->put('/'.trim($path, '/'), $file);

        if (! $type) {
            $extension = strtolower($file->getClientOriginalExtension());
            $type = in_array($extension, ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp']) ? 'image' : $extension;
        }

        if (Storage::exists($media->src)) {
            Storage::delete($media->src);
        }

        $media->update([
            'type' => $type,
            'src' => $url,
            'name' => $file->getClientOriginalName(),
            'extension' => strtolower($file->getClientOriginalExtension()),
        ]);

        return $media;
    }

    /**
     * Update an existing media record or store a new one from an uploaded file.
     *
     * If a media record is provided, its file will be replaced.
     * Otherwise, a new media record will be created and stored.
     *
     * @param  UploadedFile  $file  The uploaded file to store or replace the existing file with.
     * @param  string  $path  The path where the file will be stored.
     * @param  string|null  $type  The media type. If null, it will be determined automatically.
     * @param  Media|null  $media  The existing media record to update, or null to create a new one.
     * @return Media The updated or newly created media record.
     */
    public static function updateOrStoreByRequest(UploadedFile $file, string $path, ?string $type = null, ?Media $media = null): Media
    {
        if ($media) {
            return self::updateByRequest($file, $path, $type, $media);
        }

        return self::storeByRequest($file, $path, $type);
    }

    /**
     * Delete the stored file and the media record.
     *
     * @param  Media  $media  The media record to delete.
     * @return bool Whether the media record was deleted.
     */
    public static function deleteByRequest(Media $media): bool
    {
        if ($media->src && Storage::exists($media->src)) {
            Storage::delete($media->src);
        }

        return (bool) $media->delete();
    }
}
