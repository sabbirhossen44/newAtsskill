<?php

namespace App\Jobs;


use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailTemplate;
use App\Mail\WithdrawApprovalMail;

class WithdrawApprovalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $mail_user;
    private $status;

    public function __construct($mail_user, $status)
    {
        $this->mail_user = $mail_user;
        $this->status = $status;
    }

    public function handle(): void
    {
        $template = EmailTemplate::where('name', $this->status.'_withdraw')->first();
        $subject = $template->subject;
        $message = str_replace('{{user_name}}', $this->mail_user->name, $template->message);

        try {
            Mail::to($this->mail_user->email)->send(new WithdrawApprovalMail($subject, $message));
        } catch (Exception $ex) {
            logger($ex);
        }
    }
}
