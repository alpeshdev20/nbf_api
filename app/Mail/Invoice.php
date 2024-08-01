<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Invoice extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $plan;
    public $subscriber;
    public $transaction_id;

    public function __construct($user, $plan, $subscriber, $transaction_id)
    {
        $this->user = $user;
        $this->plan = $plan;
        $this->subscriber = $subscriber;
        $this->transaction_id = $transaction_id;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your payment has been successful',
            to: [new Address($this->user->email)] ,

        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email-templates.payment-success',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath(public_path('uploads/invoices/'.$this->transaction_id . '.pdf'))
            ->as('invoice.pdf')
            ->withMime('application/pdf'),
        ];
    }
}
