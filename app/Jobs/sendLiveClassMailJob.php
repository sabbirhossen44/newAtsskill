<?php

namespace App\Jobs;

use App\Mail\sendLiveClassMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class sendLiveClassMailJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $mail_email;
    public $mail_subject;

    public $mail_message;

    public function __construct($mail_email, $mail_subject, $mail_message) {
        $this->mail_email = $mail_email;
        $this->mail_subject = $mail_subject;
        $this->mail_message = $mail_message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        \App\Http\Controllers\Concerns\SendsMailHelper::apply();

        try {
            Mail::to($this->mail_email)->send(new sendLiveClassMail($this->mail_subject, $this->mail_message));
        } catch (\Exception $ex) {
        }
    }
}
