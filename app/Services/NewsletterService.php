<?php

namespace App\Services;

use App\Http\Controllers\Concerns\SendsMail;
use App\Jobs\NewsletterVerifyJob;
use App\Mail\NewsletterVerifyMail;
use App\Models\NewsLetter;
use App\Repositories\EmailTemplateRepository;
use Illuminate\Support\Facades\Mail;

/**
 * Newsletter verification mail, moved out of NewsletterController.
 */
class NewsletterService
{
    use SendsMail;

    public static function sendVerificationMail(NewsLetter $newsletter): void
    {
        if (! static::setMailConfig()) {
            return;
        }

        try {
            if (static::isQueable()) {
                dispatch(new NewsletterVerifyJob($newsletter));
            } else {
                $template = EmailTemplateRepository::findByName('subscribe_notification');
                Mail::to($newsletter->email)->send(new NewsletterVerifyMail($newsletter, $template->subject, $template->message));
            }
        } catch (\Exception $e) {
            \Log::error('Newsletter verify mail error: '.$e->getMessage());
        }
    }
}
