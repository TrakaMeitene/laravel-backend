<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CancelledBooking extends Mailable
{
    use Queueable, SerializesModels;
public $booking;

public $request;
public $client;
    /**
     * Create a new message instance.
     */
    public function __construct($booking, $request, $client)
    {
        $this->booking = $booking;
        $this->request = $request;
        $this->client = $client;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pieraksts pie atcelta vizīte',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.cancelledbooking',with: [
                'booking'=> $this->booking,
                'request' => $this->request,
                'client' => $this->client,
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
