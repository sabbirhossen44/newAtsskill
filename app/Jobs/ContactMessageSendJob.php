<?php

namespace App\Jobs;


use Illuminate\Support\Facades\Cache;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;
use App\Models\EmailTemplate;

class ContactMessageSendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $contact_message;

    public function __construct($contact_message)
    {
        $this->contact_message = $contact_message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \App\Http\Controllers\Concerns\SendsMailHelper::apply();

        try {
            $template = EmailTemplate::where('name', 'contact_mail')->first();
            $subject = $template->subject;
            $message = $template->message;
            $message = str_replace('{{name}}', $this->contact_message->name, $message);
            $message = str_replace('{{email}}', $this->contact_message->email, $message);
            $message = str_replace('{{phone}}', $this->contact_message->phone, $message);
            $message = str_replace('{{subject}}', $this->contact_message->subject, $message);
            $message = str_replace('{{message}}', $this->contact_message->message, $message);

            $email_setting = (object) \App\Services\SettingService::all();

            Mail::to($email_setting->contact_message_receiver_mail)->send(new ContactMail($subject, $message));
        } catch (Exception $ex) {
        }
    }
}
