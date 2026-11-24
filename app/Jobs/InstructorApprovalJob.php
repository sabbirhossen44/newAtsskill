<?php

namespace App\Jobs;


use App\Http\Controllers\Concerns\SendsMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use App\Mail\InstructorApprovalMail;

class InstructorApprovalJob implements ShouldQueue
{
    use SendsMail;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;
    public $subject;
    public $template;
    /**
     * Create a new job instance.
     */
    public function __construct($email, $subject, $template)
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->template = $template;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \App\Http\Controllers\Concerns\SendsMailHelper::apply();
        Mail::to($this->email)->send(new InstructorApprovalMail($this->subject, $this->template));
    }
}
