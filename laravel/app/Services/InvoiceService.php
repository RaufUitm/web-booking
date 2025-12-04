<?php

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Centralized storage directory for invoices.
     * Defaults to storage/app/invoices. Override with INVOICE_STORAGE_DIR in .env
     */
    private function storageDir(): string
    {
        return env('INVOICE_STORAGE_DIR', 'invoices');
    }
    /**
     * Generate invoice PDF for a booking
     */
    public function generateInvoice(Booking $booking): string
    {
        // Load booking with relationships
        $booking->load(['facility.category', 'user', 'payment']);

        // Prepare invoice data
        $data = [
            'booking' => $booking,
            'invoice_number' => $this->generateInvoiceNumber($booking),
            'invoice_date' => now()->format('d/m/Y'),
            'pbt_name' => $this->getPBTName($booking->facility->district),
            'pbt_address' => $this->getPBTAddress($booking->facility->district),
            'pbt_phone' => $this->getPBTPhone($booking->facility->district),
            'pbt_email' => $this->getPBTEmail($booking->facility->district),
            'district_color' => $this->getDistrictColor($booking->facility->district),
            'pbt_logo' => $this->getPBTLogo($booking->facility->district),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('invoices.booking-invoice', $data);
        
        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

        // Generate filename (fallback to booking ID if reference missing)
        $ref = $booking->booking_reference ?: $booking->id;
        $filename = 'invoice-' . $ref . '.pdf';
        // Save under centralized storage directory (storage/app/private)
        $dir = $this->storageDir();
        $path = $dir . '/' . $filename;

        // Save PDF to storage
        Storage::makeDirectory($dir);
        Storage::put($path, $pdf->output());

        // Clean up any legacy duplicate paths
        $this->cleanupOldPaths($filename, $path);

        return $path;
    }

    /**
     * Get district brand color
     */
    private function getDistrictColor(string $district): string
    {
        $map = [
            'besut' => '#DC143C',
            'setiu' => '#8B7355',
            'hulu_terengganu' => '#FF8C00',
            'hulu terengganu' => '#FF8C00',
            'marang' => '#8B008B',
            'kuala_terengganu' => '#EEBF04',
            'kuala terengganu' => '#EEBF04',
            'kemaman' => '#1E3A8A',
            'dungun' => '#06B6D4',
            // Abbreviations if used in DB
            'mbkt' => '#EEBF04',
            'mpkn' => '#EEBF04',
            'mpk' => '#1E3A8A',
            'mpd' => '#06B6D4',
            'mdht' => '#FF8C00',
            'mdht' => '#FF8C00',
            'mdm' => '#8B008B',
            'mds' => '#8B7355',
            'mdb' => '#DC143C',
        ];

        $key = strtolower(trim($district));
        return $map[$key] ?? '#FF8C00';
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
        $map = [
            'besut' => 'Majlis Daerah Besut',
            'setiu' => 'Majlis Daerah Setiu',
            'hulu_terengganu' => 'Majlis Daerah Hulu Terengganu',
            'hulu terengganu' => 'Majlis Daerah Hulu Terengganu',
            'marang' => 'Majlis Daerah Marang',
            'kuala_terengganu' => 'Majlis Bandaraya Kuala Terengganu',
            'kuala terengganu' => 'Majlis Bandaraya Kuala Terengganu',
            'kemaman' => 'Majlis Perbandaran Kemaman',
            'dungun' => 'Majlis Perbandaran Dungun',
            // Abbreviations
            'mbkt' => 'Majlis Bandaraya Kuala Terengganu',
            'mpk' => 'Majlis Perbandaran Kemaman',
            'mpd' => 'Majlis Perbandaran Dungun',
            'mdht' => 'Majlis Daerah Hulu Terengganu',
            'mdht' => 'Majlis Daerah Hulu Terengganu',
            'mdm' => 'Majlis Daerah Marang',
            'mds' => 'Majlis Daerah Setiu',
            'mdb' => 'Majlis Daerah Besut',
        ];
        $key = strtolower(trim($district));
        return $map[$key] ?? 'Pihak Berkuasa Tempatan';
    }

    /**
     * Get PBT address by district
     */
    private function getPBTAddress(string $district): string
    {
        $map = [
            'besut' => 'Jalan Taman Bandar, 22200 Besut, Terengganu',
            'setiu' => 'Jalan Permaisuri, 22100 Setiu, Terengganu',
            'hulu_terengganu' => 'Jalan Bukit Payung, 21000 Kuala Berang, Terengganu',
            'hulu terengganu' => 'Jalan Bukit Payung, 21000 Kuala Berang, Terengganu',
            'marang' => 'Jalan Tok Pelam, 21600 Marang, Terengganu',
            'kuala_terengganu' => 'Jalan Sultan Ismail, 20000 Kuala Terengganu',
            'kuala terengganu' => 'Jalan Sultan Ismail, 20000 Kuala Terengganu',
            'kemaman' => 'Jalan Sultan Ahmad, 24000 Kemaman, Terengganu',
            'dungun' => 'Jalan Taman Negara, 23000 Dungun, Terengganu',
            // Abbreviations
            'mbkt' => 'Jalan Sultan Ismail, 20000 Kuala Terengganu',
            'mpk' => 'Jalan Sultan Ahmad, 24000 Kemaman, Terengganu',
            'mpd' => 'Jalan Taman Negara, 23000 Dungun, Terengganu',
            'mdht' => 'Jalan Bukit Payung, 21000 Kuala Berang, Terengganu',
            'mdht' => 'Jalan Bukit Payung, 21000 Kuala Berang, Terengganu',
            'mdm' => 'Jalan Tok Pelam, 21600 Marang, Terengganu',
            'mds' => 'Jalan Permaisuri, 22100 Setiu, Terengganu',
            'mdb' => 'Jalan Taman Bandar, 22200 Besut, Terengganu',
        ];
        $key = strtolower(trim($district));
        return $map[$key] ?? 'Terengganu, Malaysia';
    }

    /**
     * Get PBT phone by district
     */
    private function getPBTPhone(string $district): string
    {
        $map = [
            'besut' => '609 6956 388',
            'setiu' => '+609-609 9377',
            'hulu_terengganu' => '+609-6811 149',
            'hulu terengganu' => '+609-6811 149',
            'marang' => '+609 618 2366',
            'kuala_terengganu' => '+609-612 1111',
            'kuala terengganu' => '+609-612 1111',
            'kuala_nerus' => '+609-612 1111',
            'kuala nerus' => '+609-612 1111',
            'kemaman' => '+609-8597 777',
            'dungun' => '+609-8481931',
            // Abbreviations
            'mbkt' => '+609-612 1111',
            'mpkn' => '+609-612 1111',
            'mpk' => '+609-8597 777',
            'mpd' => '+609-8481931',
            'mdht' => '+609-6811 149',
            'mdht' => '+609-6811 149',
            'mdm' => '+609 618 2366',
            'mds' => '+609-609 9377',
            'mdb' => '609 6956 388',
        ];
        $key = strtolower(trim($district));
        return $map[$key] ?? '+609-6811 149';
    }

    /**
     * Get PBT email by district
     */
    private function getPBTEmail(string $district): string
    {
        $map = [
            'besut' => 'info@mdb.terengganu.gov.my',
            'setiu' => 'info@mds.terengganu.gov.my',
            'hulu_terengganu' => 'info@mdht.terengganu.gov.my',
            'hulu terengganu' => 'info@mdht.terengganu.gov.my',
            'marang' => 'info@mdm.terengganu.gov.my',
            'kuala_terengganu' => 'info@mbkt.terengganu.gov.my',
            'kuala terengganu' => 'info@mbkt.terengganu.gov.my',
            'kemaman' => 'info@mpk.terengganu.gov.my',
            'dungun' => 'info@mpd.terengganu.gov.my',
            // Abbreviations
            'mbkt' => 'info@mbkt.terengganu.gov.my',
            'mpk' => 'info@mpk.terengganu.gov.my',
            'mpd' => 'info@mpd.terengganu.gov.my',
            'mdht' => 'info@mdht.terengganu.gov.my',
            'mdht' => 'info@mdht.terengganu.gov.my',
            'mdm' => 'info@mdm.terengganu.gov.my',
            'mds' => 'info@mds.terengganu.gov.my',
            'mdb' => 'info@mdb.terengganu.gov.my',
        ];
        $key = strtolower(trim($district));
        return $map[$key] ?? 'info@mdht.terengganu.gov.my';
    }

    /**
     * Get PBT logo path by district (served from public/images)
     */
    private function getPBTLogo(string $district): string
    {
        $map = [
            'besut' => 'MDB',
            'setiu' => 'MDS',
            'hulu_terengganu' => 'MDHT',
            'hulu terengganu' => 'MDHT',
            'marang' => 'MDM',
            'kuala_terengganu' => 'MBKT',
            'kuala terengganu' => 'MBKT',
            'kuala_nerus' => 'MBKT',
            'kuala nerus' => 'MBKT',
            'kemaman' => 'MPK',
            'dungun' => 'MPD',
            // Abbreviations
            'mbkt' => 'MBKT',
            'mpkn' => 'MBKT',
            'mpk' => 'MPK',
            'mpd' => 'MPD',
            'mdhulu' => 'MDHT',
            'mdht' => 'MDHT',
            'mdm' => 'MDM',
            'mds' => 'MDS',
            'mdb' => 'MDB',
        ];

        $key = strtolower(trim($district));
        $abbr = $map[$key] ?? 'terengganu-flag-bw';
        $logoPath = public_path("images/{$abbr}.png");
        
        // If logo doesn't exist, use default Terengganu flag
        if (!file_exists($logoPath)) {
            $logoPath = public_path("images/terengganu-flag-bw.png");
        }
        
        return $logoPath;
    }

    /**
     * Remove previously saved duplicates from old directories
     */
    private function cleanupOldPaths(string $filename, string $currentFullPath): void
    {
        $legacyDirs = [
            'invoices',
            'private',
            'private/private',
            'private/invoices',
            'private/private/invoices',
        ];
        foreach ($legacyDirs as $dir) {
            $legacyPath = $dir . '/' . $filename;
            if ($legacyPath !== $currentFullPath && Storage::exists($legacyPath)) {
                Storage::delete($legacyPath);
            }
        }
    }

    /**
     * Get invoice path for a booking
     */
    public function getInvoicePath(Booking $booking): ?string
    {
        // Match generation logic with same fallback
        $ref = $booking->booking_reference ?: $booking->id;
        $filename = 'invoice-' . $ref . '.pdf';
        $path = $this->storageDir() . '/' . $filename;

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
