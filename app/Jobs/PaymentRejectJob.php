<?php

namespace App\Jobs;


use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentRejectMail;

class PaymentRejectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $mail_subject;

    private $mail_template;

    private $mail_user;

    public function __construct($mail_subject, $mail_template, $mail_user)
    {
        $this->mail_subject = $mail_subject;
        $this->mail_template = $mail_template;
        $this->mail_user = $mail_user;
    }

    public function handle(): void
    {
        \App\Http\Controllers\Concerns\SendsMailHelper::apply();

        $mail_description = str_replace('[[name]]', $this->mail_user->name, $this->mail_template);

        try {
            Mail::to($this->mail_user->email)->send(new PaymentRejectMail($this->mail_subject, $mail_description));
        } catch (Exception $ex) {
        }

    }
}
