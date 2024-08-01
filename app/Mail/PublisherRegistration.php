<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PublisherRegistration extends Mailable
{
    use Queueable, SerializesModels;

    public $taskDetails;


    public function __construct($taskDetails)
    {
        $this->taskDetails = $taskDetails;
    }



    public function build()
    {
        $mail = $this->view('email-templates.publishers.publisher_registration')
            ->subject($this->taskDetails['subject']);


        //* Attachment
        if (isset($this->taskDetails['attachment'])) {
            $mail->attach($this->taskDetails['attachment']->getPathname(), [
                'as' => $this->taskDetails['attachment']->getClientOriginalName(),
                'mime' => $this->taskDetails['attachment']->getMimeType(),
            ]);
        }

        return $mail;
    }
}
