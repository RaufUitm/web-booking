<?php

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate invoice PDF for a booking
     */
    public function generateInvoice(Booking $booking): string
    {
        // Load booking with relationships
        $booking->load(['facility', 'user', 'payment']);

        // Prepare invoice data
        $data = [
            'booking' => $booking,
            'invoice_number' => $this->generateInvoiceNumber($booking),
            'invoice_date' => now()->format('d/m/Y'),
            'pbt_name' => $this->getPBTName($booking->facility->district),
            'pbt_address' => $this->getPBTAddress($booking->facility->district),
            'pbt_phone' => $this->getPBTPhone($booking->facility->district),
            'pbt_email' => $this->getPBTEmail($booking->facility->district),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('invoices.booking-invoice', $data);
        
        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

        // Generate filename
        $filename = 'invoice-' . $booking->booking_reference . '.pdf';
        $path = 'invoices/' . $filename;

        // Save PDF to storage
        Storage::put($path, $pdf->output());

        return $path;
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(Booking $booking): string
    {
        $district = strtoupper(substr($booking->facility->district, 0, 3));
        $date = now()->format('Ymd');
        $bookingId = str_pad($booking->id, 6, '0', STR_PAD_LEFT);
        
        return "INV-{$district}-{$date}-{$bookingId}";
    }

    /**
     * Get PBT name by district
     */
    private function getPBTName(string $district): string
    {
        $names = [
            'besut' => 'Majlis Daerah Besut',
            'setiu' => 'Majlis Daerah Setiu',
            'hulu_terengganu' => 'Majlis Daerah Hulu Terengganu',
            'marang' => 'Majlis Daerah Marang',
            'kuala_terengganu' => 'Majlis Bandaraya Kuala Terengganu',
            'kuala_nerus' => 'Majlis Perbandaran Kuala Nerus',
            'dungun' => 'Majlis Perbandaran Dungun',
        ];

        return $names[strtolower($district)] ?? 'Pihak Berkuasa Tempatan';
    }

    /**
     * Get PBT address by district
     */
    private function getPBTAddress(string $district): string
    {
        $addresses = [
            'besut' => 'Jalan Taman Bandar, 22200 Besut, Terengganu',
            'setiu' => 'Jalan Permaisuri, 22100 Setiu, Terengganu',
            'hulu_terengganu' => 'Jalan Bukit Payung, 21000 Kuala Berang, Terengganu',
            'marang' => 'Jalan Tok Pelam, 21600 Marang, Terengganu',
            'kuala_terengganu' => 'Jalan Sultan Ismail, 20000 Kuala Terengganu',
            'kuala_nerus' => 'Jalan Dato\' Isaac, 21300 Kuala Nerus, Terengganu',
            'dungun' => 'Jalan Taman Negara, 23000 Dungun, Terengganu',
        ];

        return $addresses[strtolower($district)] ?? 'Terengganu, Malaysia';
    }

    /**
     * Get PBT phone by district
     */
    private function getPBTPhone(string $district): string
    {
        return '09-XXX XXXX'; // Replace with actual phone numbers
    }

    /**
     * Get PBT email by district
     */
    private function getPBTEmail(string $district): string
    {
        $slug = strtolower(str_replace(' ', '', $district));
        return "info@{$slug}.gov.my";
    }

    /**
     * Get invoice path for a booking
     */
    public function getInvoicePath(Booking $booking): ?string
    {
        $filename = 'invoice-' . $booking->booking_reference . '.pdf';
        $path = 'invoices/' . $filename;

        return Storage::exists($path) ? $path : null;
    }

    /**
     * Delete invoice file
     */
    public function deleteInvoice(Booking $booking): bool
    {
        $path = $this->getInvoicePath($booking);
        
        if ($path && Storage::exists($path)) {
            return Storage::delete($path);
        }

        return false;
    }
}
