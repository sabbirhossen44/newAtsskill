<?php

namespace App\Http\Controllers\Concerns;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

/**
 * Port of the old App\Traits\MailSenderTrait.
 * Mail SMTP settings come from the settings table (via setting()).
 */
trait SendsMail
{
    protected static function isQueable(): bool
    {
        return setting('is_queable') === 'active';
    }

    protected static function setMailConfig(): bool
    {
        try {
            $email_setting = (object) \App\Services\SettingService::all();

            $mailConfig = [
                'transport' => 'smtp',
                'host' => $email_setting->mail_host ?? config('mail.mailers.smtp.host'),
                'port' => $email_setting->mail_port ?? config('mail.mailers.smtp.port'),
                'encryption' => $email_setting->mail_encryption ?? config('mail.mailers.smtp.encryption'),
                'username' => $email_setting->mail_username ?? config('mail.mailers.smtp.username'),
                'password' => $email_setting->mail_password ?? config('mail.mailers.smtp.password'),
                'timeout' => null,
            ];

            config(['mail.mailers.smtp' => $mailConfig]);
            config(['mail.from.address' => $email_setting->mail_sender_email ?? config('mail.from.address')]);
            config(['mail.from.name' => $email_setting->mail_sender_name ?? config('mail.from.name')]);

            return true;
        } catch (Exception $e) {
            if (app()->isLocal()) {
                Log::error($e->getMessage());
            }

            return false;
        }
    }

    /** Port of the old GetGlobalInformationTrait::set_mail_config(). */
    protected function set_mail_config(): void
    {
        static::setMailConfig();
    }
}
