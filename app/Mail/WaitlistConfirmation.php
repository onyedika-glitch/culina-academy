<?php

namespace App\Mail;

use App\Models\WaitingList;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public WaitingList $entry, public ?string $courseTitle = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Waitlist Confirmation — Culina Academy',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.waitlist-confirmation',
            with: [
                'entry' => $this->entry,
                'courseTitle' => $this->courseTitle,
            ],
        );
    }
}
