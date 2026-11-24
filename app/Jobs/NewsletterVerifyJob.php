<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\EmailTemplate;
use App\Mail\NewsletterVerifyMail;

class NewsletterVerifyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $newsletter_info;

    public function __construct($newsletter_info)
    {
        $this->newsletter_info = $newsletter_info;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        \App\Http\Controllers\Concerns\SendsMailHelper::apply();

        try {
            $template = EmailTemplate::where('name', 'subscribe_notification')->first();
            $message = $template->message;
            $subject = $template->subject;
            Mail::to($this->newsletter_info->email)->send(new NewsletterVerifyMail($this->newsletter_info, $subject, $message));
        } catch (Exception $ex) {
        }

    }
}
