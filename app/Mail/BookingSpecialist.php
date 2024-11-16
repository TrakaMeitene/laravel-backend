<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingSpecialist extends Mailable
{
    use Queueable, SerializesModels;
public $booking;
public $user;

public $request;
public $servicetime;

    /**
     * Create a new message instance.
     */
    public function __construct($booking, $user, $request, $servicetime)
    {
        $this->booking = $booking;
        $this->user = $user;
        $this->request = $request;
        $this->servicetime = $servicetime;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pieraksts pie jauns pieraksts',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bookingspecialist',with: [
                'booking'=> $this->booking,
                'user' => $this->user,
                'request' => $this->request,
                'service' => $this->servicetime
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
