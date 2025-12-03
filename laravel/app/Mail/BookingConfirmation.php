<?php

namespace App\Mail;

use App\Models\Booking;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    private $invoicePath;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, ?string $invoicePath = null)
    {
        $this->booking = $booking->load(['facility', 'user']);
        $this->invoicePath = $invoicePath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmation - ' . $this->booking->facility->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmation',
            with: [
                'booking' => $this->booking,
                'facilityName' => $this->booking->facility->name,
                'bookingDate' => $this->booking->booking_date,
                'timeSlot' => ($this->booking->booking_type === 'per_day') ? 'Sepanjang Hari' : (($this->booking->start_time ?? '') . ' - ' . ($this->booking->end_time ?? '')),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Attach invoice PDF if path is provided
        if ($this->invoicePath && Storage::exists($this->invoicePath)) {
            $attachments[] = Attachment::fromStorage($this->invoicePath)
                ->as('invoice-' . $this->booking->booking_reference . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
