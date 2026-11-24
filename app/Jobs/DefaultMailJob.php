<?php

namespace App\Jobs;

use App\Mail\DefaultMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class DefaultMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $mailData;
    public $subject;
    public $messageTemplate;
    /**
     * Create a new job instance.
     */
    public function __construct($mailData, $messageTemplate = null, $extra = null)
    {
        if (is_string($mailData) && is_array($messageTemplate)) {
            // Case where ($email, $mailData, $messageTemplate) was passed
            $actualData = $messageTemplate;
            $actualTemplate = $extra;
            if (!isset($actualData['email'])) {
                $actualData['email'] = $mailData;
            }
            $mailData = $actualData;
            $messageTemplate = $actualTemplate;
        }

        $this->mailData = is_array($mailData) ? $mailData : ['email' => $mailData];
        $this->messageTemplate = $messageTemplate;
        $this->subject = $this->mailData['subject'] ?? 'Notification';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $email = $this->mailData['email'] ?? null;
        if ($email) {
            Mail::to($email)->send(new DefaultMail($this->mailData, $this->messageTemplate));
        }
    }
}
