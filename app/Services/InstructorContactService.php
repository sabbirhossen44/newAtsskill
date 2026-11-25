<?php

namespace App\Services;

use App\Http\Controllers\Concerns\SendsMail;
use App\Jobs\DefaultMailJob;
use App\Mail\DefaultMail;
use App\Models\User;
use App\Repositories\EmailTemplateRepository;
use Illuminate\Support\Facades\Mail;

/**
 * Instructor "quick connect" mail, moved out of HomePageController.
 * Note: the old controller called Mail::to() without importing the Mail
 * facade, so the non-queued branch fatalled on an undefined class;
 * this service imports the facade, which is what the code intended.
 */
class InstructorContactService
{
    use SendsMail;

    public static function sendQuickConnect(array $validated, User $instructor): void
    {
        try {
            static::setMailConfig();
            $template = EmailTemplateRepository::findByName('instructor_quick_contact');
            $subject = $template?->subject ?? $validated['subject'];
            $message = $template ? str_replace(
                ['{{name}}', '{{email}}', '{{subject}}', '{{message}}'],
                [$validated['name'], $validated['email'], $validated['subject'], $validated['message']],
                $template->message
            ) : $validated['message'];

            if (static::isQueable()) {
                DefaultMailJob::dispatch(['email' => $instructor->email, 'subject' => $subject], $message);
            } else {
                Mail::to($instructor->email)->send(new DefaultMail(['email' => $instructor->email, 'subject' => $subject], $message));
            }
        } catch (\Exception $e) {
            \Log::error('Quick connect mail error: '.$e->getMessage());
        }
    }
}
