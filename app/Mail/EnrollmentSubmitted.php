<?php

namespace App\Mail;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnrollmentSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enrollment $enrollment, public ?string $courseTitle = null) {}

    public function envelope(): Envelope
    {
        $name = trim($this->enrollment->first_name . ' ' . $this->enrollment->last_name);

        return new Envelope(
            subject: 'Course Enrollment: ' . $name,
            replyTo: [$this->enrollment->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.enrollment-submitted',
            with: [
                'enrollment' => $this->enrollment,
                'courseTitle' => $this->courseTitle,
            ],
        );
    }
}