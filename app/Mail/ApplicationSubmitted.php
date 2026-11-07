<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Application $application) {}

    public function envelope(): Envelope
    {
        $name = trim($this->application->first_name . ' ' . $this->application->surname);

        return new Envelope(
            subject: 'New Course Application from ' . $name,
            replyTo: [$this->application->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.application-submitted',
            with: ['application' => $this->application],
        );
    }
}