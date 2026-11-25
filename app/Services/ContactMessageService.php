<?php

namespace App\Services;

use App\Http\Controllers\Concerns\SendsMail;
use App\Jobs\ContactMessageSendJob;
use App\Mail\ContactMail;
use App\Models\ContactMessage;
use App\Repositories\EmailTemplateRepository;
use Illuminate\Support\Facades\Mail;

/**
 * Contact-page mail sending, moved out of ContactController.
 * The template lookup, placeholder replacement and queue/direct
 * branches are verbatim from the old controller.
 */
class ContactMessageService
{
    use SendsMail;

    public static function send(array $validated, ContactMessage $contact): void
    {
        // Preserved verbatim from the old controller: $marketingSettings is
        // always an empty object, so this branch never fires.
        $settings = (object) SettingService::all();
        $marketingSettings = (object) [];
        if ($settings->google_tagmanager_status == 'active' && $marketingSettings->contact_page) {
            session()->put('contactUs', [
                'name' => $contact->name,
                'email' => $contact->email,
                'subject' => $contact->subject,
                'message' => $contact->message,
            ]);
        }

        static::setMailConfig();

        $template = EmailTemplateRepository::findByName('contact_mail');
        $subject = $template->subject;
        $message = $template->message;
        $message = str_replace('{{name}}', $validated['name'], $message);
        $message = str_replace('{{email}}', $validated['email'], $message);
        $message = str_replace('{{phone}}', $validated['phone'] ?? null, $message);
        $message = str_replace('{{subject}}', $validated['subject'], $message);
        $message = str_replace('{{message}}', $validated['message'], $message);

        $email_setting = (object) SettingService::all();

        if (static::isQueable()) {
            ContactMessageSendJob::dispatch(collect($validated));
        } else {
            Mail::to($email_setting->contact_message_receiver_mail)->send(new ContactMail($subject, $message));
        }
    }
}
