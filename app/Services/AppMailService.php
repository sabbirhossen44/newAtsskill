<?php

namespace App\Services;

use App\Http\Controllers\Concerns\SendsAppMail;

/**
 * Application mail sender (allSolution style: mail logic lives in a
 * service, not in controller traits). The template-driven sending
 * itself still lives in the SendsAppMail concern, exactly as before;
 * this service is the single entry point controllers and repositories
 * call, with plain static methods.
 */
class AppMailService
{
    use SendsAppMail;

    public static function sendVerifyMailToUser(string $userType, $userInfo = null): bool
    {
        return (new static)->sendVerifyMailToUserFromTrait($userType, $userInfo);
    }

    public static function sendUserForgetPassword($user, string $mailTemplatePath = 'auth'): bool
    {
        return (new static)->sendUserForgetPasswordFromTrait($user, $mailTemplatePath);
    }

    public static function sendSocialLoginDefaultPassword($user, string $password): bool
    {
        return (new static)->sendSocialLoginDefaultPasswordFromTrait($user, $password);
    }

    public static function sendQnaReplyMail($question): bool
    {
        return (new static)->sendQnaReplyMailTrait($question);
    }

    public static function sendLiveClassNotificationMail($users, $data): bool
    {
        return (new static)->sendLiveClassNotificationMailTrait($users, $data);
    }

    /** Apply the SMTP settings from the settings table. */
    public static function configureMail(): bool
    {
        return static::setMailConfig();
    }

    /** Whether mail is sent through the queue. */
    public static function isQueable(): bool
    {
        return static::isQueableMail();
    }

    protected static function isQueableMail(): bool
    {
        return setting('is_queable') === 'active';
    }
}
