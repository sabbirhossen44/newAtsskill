<?php

namespace App\Jobs;

use Exception;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\SendMailToUser;

class SendBulkMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $mail_subject;

    private $mail_message;

    private $user_type;

    private $user_info;

    public function __construct($mail_subject, $mail_message, $user_type, $user_info = null)
    {
        $this->mail_subject = $mail_subject;
        $this->mail_message = $mail_message;
        $this->user_type = $user_type;
        $this->user_info = $user_info;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        \App\Http\Controllers\Concerns\SendsMailHelper::apply();

        if ($this->user_type == 'all_user') {
            $users = User::where(['status' => 'active', 'is_banned' => 'no'])->where('email_verified_at', '!=', null)->orderBy('id', 'desc')->get();

            foreach ($users as $index => $user) {
                try {
                    Mail::to($user->email)->send(new SendMailToUser($this->mail_message, $this->mail_subject));
                } catch (Exception $ex) {
                    logger($ex);
                }
            }
        } else {
            try {
                Mail::to($this->user_info->email)->send(new SendMailToUser($this->mail_message, $this->mail_subject));
            } catch (Exception $ex) {
                logger($ex);
            }
        }
    }
}
