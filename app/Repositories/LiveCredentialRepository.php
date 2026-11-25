<?php

namespace App\Repositories;

use App\Models\JitsiSetting;
use App\Models\User;
use App\Models\ZoomCredential;
use Illuminate\Http\UploadedFile;

class LiveCredentialRepository
{
    public static function zoomCredentialFor(User $user): ?ZoomCredential
    {
        return $user->zoom_credential;
    }

    public static function updateZoom(int $userId, array $data): ZoomCredential
    {
        return ZoomCredential::updateOrCreate(['instructor_id' => $userId], $data);
    }

    public static function jitsiCredentialFor(User $user): ?JitsiSetting
    {
        return $user->jitsi_credential;
    }

    /**
     * Store the RSA private key file (storage/app/user_{id}/rsb_private_key.{ext},
     * replacing any existing one) and upsert the Jitsi setting.
     */
    public static function updateJitsi(int $userId, array $data, ?UploadedFile $privateKey = null): JitsiSetting
    {
        // The uploaded key is stored as a file only; jitsi_settings has no
        // private_key column (the old controller passed the file into
        // updateOrCreate, which failed on save when a file was uploaded).
        unset($data['private_key']);

        if ($privateKey) {
            $extension = $privateKey->getClientOriginalExtension();
            $storage_path = storage_path("app/user_{$userId}");
            $file_name = 'rsb_private_key.'.$extension;
            $full_file_path = "{$storage_path}/{$file_name}";

            if (! is_dir($storage_path)) {
                mkdir($storage_path, 0777, true);
            } elseif (file_exists($full_file_path)) {
                unlink($full_file_path);
            }
            $privateKey->move($storage_path, $file_name);
        }

        return JitsiSetting::updateOrCreate(['instructor_id' => $userId], $data);
    }

    /** True when a private key file already exists for the instructor. */
    public static function privateKeyExists(int $userId, string $extension = 'pk'): bool
    {
        return file_exists(storage_path("app/user_{$userId}/rsb_private_key.{$extension}"));
    }
}
