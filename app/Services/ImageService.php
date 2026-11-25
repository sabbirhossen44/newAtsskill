<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

/**
 * Optimise uploaded images to WebP.
 *
 * allSolution shape (instance methods, called via
 * app(ImageService::class)->optimize($file)); Intervention is used
 * internally because the Illuminate Image facade allSolution uses is
 * not in this build. MediaRepository does NOT call this service.
 */
class ImageService
{
    public const MAX_DIMENSION = 1280;

    public const WEBP_QUALITY = 80;

    public const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function maxDimension(): int
    {
        return (int) config('images.max_dimension', self::MAX_DIMENSION);
    }

    public function quality(): int
    {
        return (int) config('images.quality', self::WEBP_QUALITY);
    }

    public function supports(UploadedFile $file): bool
    {
        return in_array(strtolower((string) $file->extension()), self::SUPPORTED_EXTENSIONS, true);
    }

    /**
     * Optimise an uploaded image to WebP (max 1280px on the longest
     * side, quality 80). Returns [$upload, $optimizedPath]: the
     * optimised file as a new UploadedFile plus the temp path the
     * caller must unlink, or [$file, null] for unsupported files.
     *
     * @return array{0: UploadedFile, 1: string|null}
     */
    public function optimize(UploadedFile $file): array
    {
        if (! $this->supports($file)) {
            return [$file, null];
        }

        $optimizedPath = $this->encode($file->getRealPath());

        if (! $optimizedPath) {
            return [$file, null];
        }

        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'image';
        $upload = new UploadedFile($optimizedPath, $name.'.webp', 'image/webp', null, true);

        return [$upload, $optimizedPath];
    }

    public function optimizePath(string $source, ?int $max = null, ?int $quality = null): ?string
    {
        if (! is_file($source)) {
            return null;
        }

        return $this->encode($source, $max, $quality);
    }

    private function encode(string $source, ?int $max = null, ?int $quality = null): ?string
    {
        try {
            $max ??= $this->maxDimension();
            $quality ??= $this->quality();

            $image = $this->manager()->read($source);
            $image->scale(width: $max, height: $max);

            $tempPath = tempnam(sys_get_temp_dir(), 'img_').'.webp';
            $image->toWebp($quality)->save($tempPath);

            return $tempPath;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function manager(): ImageManager
    {
        $driver = config('images.driver', 'gd') === 'imagick'
            ? new ImagickDriver
            : new GdDriver;

        return new ImageManager($driver);
    }
}
