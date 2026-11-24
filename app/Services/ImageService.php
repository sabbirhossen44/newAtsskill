<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

class ImageService
{
    public const MAX_DIMENSION = 1280;

    public const WEBP_QUALITY = 80;

    public const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public static function isSupported(UploadedFile $file): bool
    {
        return in_array(strtolower($file->getClientOriginalExtension()), static::SUPPORTED_EXTENSIONS, true);
    }

    protected static function manager(): ImageManager
    {
        $driver = config('images.driver', 'gd') === 'imagick'
            ? new ImagickDriver
            : new GdDriver;

        return new ImageManager($driver);
    }

    /**
     * Optimise an uploaded image to WebP (max 1280px on the longest
     * side, quality 80). Returns [$originalFile, $optimizedTempPath|null].
     * Non-supported files pass through untouched; the caller must
     * unlink the temp file (MediaRepository does this in a finally).
     *
     * @return array{0: UploadedFile, 1: string|null}
     */
    public static function optimize(UploadedFile $file): array
    {
        if (! static::isSupported($file)) {
            return [$file, null];
        }

        $max = (int) config('images.max_dimension', static::MAX_DIMENSION);
        $quality = (int) config('images.quality', static::WEBP_QUALITY);

        $image = static::manager()->read($file->getRealPath());

        $image->scale(width: $max, height: $max);

        $tempPath = tempnam(sys_get_temp_dir(), 'img_').'.webp';

        $image->toWebp($quality)->save($tempPath);

        return [$file, $tempPath];
    }
}
