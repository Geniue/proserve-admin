<?php

namespace App\Mail;

use App\Http\Controllers\ReceiptController;
use App\Models\ServiceBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ServiceBooking $booking)
    {
        $this->booking->load(['customer', 'provider.user', 'service.category', 'events']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Receipt for Booking #{$this->booking->booking_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-receipt',
            with: [
                'booking' => $this->booking,
            ],
        );
    }

    public function attachments(): array
    {
        $pdfContent = ReceiptController::generatePdfOutput($this->booking);

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdfContent,
                "receipt-{$this->booking->booking_number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
