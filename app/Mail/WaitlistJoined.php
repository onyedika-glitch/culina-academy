<?php

namespace App\Mail;

use App\Models\Waitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistJoined extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Waitlist $entry, public ?string $courseTitle = null) {}

    public function envelope(): Envelope
    {
        $name = trim($this->entry->first_name.' '.$this->entry->last_name);

        return new Envelope(
            subject: 'Waitlist Signup: '.$name,
            replyTo: [$this->entry->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.waitlist-joined',
            with: [
                'entry' => $this->entry,
                'courseTitle' => $this->courseTitle,
            ],
        );
    }
}
