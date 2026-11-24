<?php

namespace App\Http\Controllers\Concerns;

/** Static mail-config helper for Jobs (same settings as SendsMail trait). */
class SendsMailHelper
{
    public static function apply(): bool
    {
        $email_setting = (object) \App\Services\SettingService::all();

        config(['mail.mailers.smtp' => [
            'transport' => 'smtp',
            'host' => $email_setting->mail_host ?? config('mail.mailers.smtp.host'),
            'port' => $email_setting->mail_port ?? config('mail.mailers.smtp.port'),
            'encryption' => $email_setting->mail_encryption ?? config('mail.mailers.smtp.encryption'),
            'username' => $email_setting->mail_username ?? config('mail.mailers.smtp.username'),
            'password' => $email_setting->mail_password ?? config('mail.mailers.smtp.password'),
            'timeout' => null,
        ]]);
        config(['mail.from.address' => $email_setting->mail_sender_email ?? config('mail.from.address')]);
        config(['mail.from.name' => $email_setting->mail_sender_name ?? config('mail.from.name')]);

        return true;
    }
}
