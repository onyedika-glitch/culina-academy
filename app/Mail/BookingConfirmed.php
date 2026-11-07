<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking, public ?string $courseTitle = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmed — Culina Academy',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.booking-confirmed',
            with: [
                'booking' => $this->booking,
                'courseTitle' => $this->courseTitle,
            ],
        );
    }
}
