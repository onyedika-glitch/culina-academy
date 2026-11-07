<?php

namespace App\Mail;

use App\Models\WaitingList;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WaitingList $entry,
        public ?string $courseTitle = null,
        public string $inviteUrl = '',
        public int $expiryHours = 48,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'A Spot Has Opened! — Culina Academy',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.waitlist-invitation',
            with: [
                'entry' => $this->entry,
                'courseTitle' => $this->courseTitle,
                'inviteUrl' => $this->inviteUrl,
                'expiryHours' => $this->expiryHours,
            ],
        );
    }
}
