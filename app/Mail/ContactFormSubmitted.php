<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $payload) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Contact Form Message from ' . ($this->payload['name'] ?? 'Website'),
            replyTo: [$this->payload['email'] ?? config('mail.from.address')],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.contact-form',
            with: ['payload' => $this->payload],
        );
    }
}