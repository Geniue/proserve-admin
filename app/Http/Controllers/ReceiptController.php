<?php

namespace App\Http\Controllers;

use App\Mail\BookingReceiptMail;
use App\Models\ServiceBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Mpdf\Mpdf;

class ReceiptController extends Controller
{
    public function view(ServiceBooking $booking)
    {
        $booking->load(['customer', 'provider.user', 'service.category', 'events']);

        return view('receipts.booking-receipt', [
            'booking' => $booking,
            'events' => $booking->events,
        ]);
    }

    public function downloadPdf(ServiceBooking $booking)
    {
        $booking->load(['customer', 'provider.user', 'service.category', 'events']);

        $html = view('receipts.booking-receipt', [
            'booking' => $booking,
            'events' => $booking->events,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'autoArabic' => true,
            'autoLangToFont' => true,
            'autoScriptToLang' => true,
            'tempDir' => storage_path('app/mpdf'),
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="receipt-' . $booking->booking_number . '.pdf"',
        ]);
    }

    public function emailReceipt(Request $request, ServiceBooking $booking)
    {
        $booking->load(['customer', 'provider.user', 'service.category', 'events']);

        $email = $request->input('email', $booking->customer?->email);

        if (!$email) {
            return back()->with('error', 'No email address available.');
        }

        Mail::to($email)->send(new BookingReceiptMail($booking));

        return back()->with('success', 'Receipt sent successfully.');
    }

    public static function generatePdfOutput(ServiceBooking $booking): string
    {
        $booking->load(['customer', 'provider.user', 'service.category', 'events']);

        $html = view('receipts.booking-receipt', [
            'booking' => $booking,
            'events' => $booking->events,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'autoArabic' => true,
            'autoLangToFont' => true,
            'autoScriptToLang' => true,
            'tempDir' => storage_path('app/mpdf'),
        ]);

        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }
}
