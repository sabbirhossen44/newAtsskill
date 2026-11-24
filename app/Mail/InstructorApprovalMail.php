<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InstructorApprovalMail extends Mailable
{
    use Queueable, SerializesModels;
    public $subject;
    public $messageTemplate;
    /**
     * Create a new message instance.
     */
    public function __construct($subject, $messageTemplate)
    {
        $this->subject = $subject;
        $this->messageTemplate = $messageTemplate;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->view('emails.default-mail-template');
    }
}
