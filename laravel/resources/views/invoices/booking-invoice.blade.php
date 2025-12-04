<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $booking->booking_reference }}</title>
    <style>
                /* Page setup for better pagination */
                @page {
                    margin: 30px 30px;
                }

                /* Control page breaks for DomPDF */
                .page-break {
                    page-break-before: always;
                }

                .avoid-break {
                    page-break-inside: avoid;
                }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }

        .container {
            padding: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid {{ $district_color }};
            padding-bottom: 20px;
        }

        .header h1 {
            color: {{ $district_color }};
            font-size: 28px;
            margin-bottom: 5px;
        }

        .header h2 {
            color: #666;
            font-size: 18px;
            font-weight: normal;
        }

        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .invoice-info-left,
        .invoice-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .invoice-info-right {
            text-align: right;
        }

        .info-block {
            margin-bottom: 15px;
        }

        .info-block h3 {
            font-size: 14px;
            color: {{ $district_color }};
            margin-bottom: 5px;
        }

        .info-block p {
            margin: 3px 0;
            color: #555;
        }

        .invoice-details {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .invoice-details table {
            width: 100%;
        }

        .invoice-details td {
            padding: 5px 0;
        }

        .invoice-details .label {
            font-weight: bold;
            color: #666;
            width: 40%;
        }

        .booking-details {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .booking-details h3 {
            background-color: {{ $district_color }};
            color: {{ $district_color == '#EEBF04' ? '#000' : 'white' }};
            padding: 10px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        .details-table th,
        .details-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .details-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #333;
        }

        .amount-section {
            margin-top: 20px;
            text-align: right;
        }

        .amount-row {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .amount-row.total {
            font-size: 18px;
            font-weight: bold;
            color: {{ $district_color }};
            padding-top: 10px;
            border-top: 2px solid #ddd;
        }

        .payment-info {
            background-color: #e8f5e9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 20px 0;
            page-break-inside: avoid;
        }

        .payment-info h3 {
            color: #4CAF50;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            color: #888;
            font-size: 10px;
        }

        .terms {
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }

        .terms h4 {
            margin-bottom: 5px;
            font-size: 12px;
        }

        .stamp {
            text-align: right;
            margin-top: 40px;
        }

        .stamp-box {
            display: inline-block;
            border: 2px solid {{ $district_color }};
            padding: 30px 50px;
            text-align: center;
        }

        .stamp-box p {
            margin: 5px 0;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <table style="width:100%; margin-bottom:10px;">
                <tr>
                    <td style="width:80px;">
                        @if(!empty($pbt_logo))
                            <img src="{{ $pbt_logo }}" alt="PBT Logo" style="height:60px; width:auto;">
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <h1>INVOIS / INVOICE</h1>
                        <h2>{{ $pbt_name }}</h2>
                        <p style="color:#666; font-size:12px; margin-top:6px;">Resit rasmi tempahan kemudahan awam / Official receipt for facility booking</p>
                    </td>
                    <td style="width:80px;"></td>
                </tr>
            </table>
        </div>

        <!-- Invoice Info -->
        <div class="invoice-info">
            <div class="invoice-info-left">
                <div class="info-block">
                    <h3>Maklumat PBT / PBT Information</h3>
                    <p><strong>{{ $pbt_name }}</strong></p>
                    <p>{{ $pbt_address }}</p>
                    <p>Tel / Tel: {{ $pbt_phone }}</p>
                    <p>Emel / Email: {{ $pbt_email }}</p>
                </div>

                <div class="info-block">
                    <h3>Bil Kepada / Bill To</h3>
                    <p><strong>{{ $booking->user->name }}</strong></p>
                    <p>{{ $booking->user->email }}</p>
                    <p>{{ $booking->user->phone ?? 'Tiada / N/A' }}</p>
                </div>
            </div>

            <div class="invoice-info-right">
                <div class="info-block">
                    <p><strong>No. Invois / Invoice Number:</strong></p>
                    <p style="font-size: 16px; color: {{ $district_color }};">{{ $invoice_number }}</p>
                </div>
                <div class="info-block">
                    <p><strong>Tarikh Invois / Invoice Date:</strong></p>
                    <p>{{ $invoice_date }}</p>
                </div>
                <div class="info-block">
                    <p><strong>Rujukan Tempahan / Booking Reference:</strong></p>
                    <p>{{ $booking->booking_reference }}</p>
                </div>
            </div>
        </div>

        <!-- Booking Details -->
        <div class="booking-details">
            <h3>BUTIRAN TEMPAHAN / BOOKING DETAILS</h3>
            <table class="details-table">
                <thead>
                    <tr>
                        <th>Keterangan / Description</th>
                        <th style="width: 30%;">Butiran / Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Nama Kemudahan / Facility Name</td>
                        <td><strong>{{ $booking->facility->name }}</strong></td>
                    </tr>
                    <tr>
                        <td>Kategori / Category</td>
                        <td>{{ $booking->facility->category->name ?? 'Tiada / N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Tarikh Tempahan / Booking Date</td>
                        <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y (l)') }}</td>
                    </tr>
                    <tr>
                        <td>Slot Masa / Time Slot</td>
                        <td>
                            @if($booking->booking_type === 'per_day')
                                Sepanjang Hari / Full Day
                            @else
                                {{ $booking->start_time }} - {{ $booking->end_time }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Jenis Tempahan / Booking Type</td>
                        <td>
                            @php
                                $typeMap = [
                                    'per_day' => 'Per Hari',
                                    'per_hour' => 'Per Jam',
                                ];
                                $typeMalay = $typeMap[$booking->booking_type] ?? ucfirst(str_replace('_', ' ', $booking->booking_type));
                            @endphp
                            {{ $typeMalay }}
                        </td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>
                            @php
                                $statusMap = [
                                    'confirmed' => 'Disahkan',
                                    'pending' => 'Menunggu',
                                    'failed' => 'Gagal',
                                    'cancelled' => 'Dibatalkan',
                                ];
                                $statusMalay = $statusMap[strtolower($booking->status)] ?? $booking->status;
                            @endphp
                            <strong style="color: #4CAF50;">{{ $statusMalay }}</strong>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Amount Section -->
            <div class="amount-section">
                <div class="amount-row">
                    <span>Subjumlah / Subtotal: </span>
                    <strong>RM {{ number_format($booking->total_amount, 2) }}</strong>
                </div>
                <div class="amount-row">
                    <span>Cukai (0%) / Tax (0%): </span>
                    <strong>RM 0.00</strong>
                </div>
                <div class="amount-row total">
                    <span>JUMLAH KESELURUHAN / TOTAL AMOUNT: </span>
                    <strong>RM {{ number_format($booking->total_amount, 2) }}</strong>
                </div>
            </div>
        </div>

        <!-- Payment Information (start on a new page for readability) -->
        @if($booking->payment)
        <div class="page-break"></div>
        <div class="payment-info avoid-break">
            <h3>✓ PEMBAYARAN DISAHKAN / PAYMENT CONFIRMED</h3>
            <table style="width: 100%;">
                <tr>
                    <td><strong>Kaedah Bayaran / Payment Method:</strong></td>
                    <td>{{ strtoupper($booking->payment->payment_method) }}</td>
                </tr>
                <tr>
                    <td><strong>ID Transaksi / Transaction ID:</strong></td>
                    <td>{{ $booking->payment->payment_reference ?? $booking->payment->transaction_id }}</td>
                </tr>
                <tr>
                    <td><strong>Tarikh Bayaran / Payment Date:</strong></td>
                    <td>{{ $booking->payment->updated_at->format('d/m/Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td><strong style="color: #4CAF50;">{{ $booking->payment->status }}</strong></td>
                </tr>
            </table>
        </div>
        @endif

        <!-- Terms and Conditions -->
        <div class="terms">
            <h4>Syarat & Peraturan / Terms & Conditions:</h4>
            <p>1. Invois ini merupakan resit rasmi tempahan kemudahan awam. / This invoice serves as an official receipt for your facility booking.</p>
            <p>2. Pengesahan tempahan tertakluk kepada kelulusan pembayaran. / Booking confirmation is subject to payment clearance.</p>
            <p>3. Pembatalan perlu dibuat sekurang-kurangnya 48 jam sebelum tarikh tempahan untuk kelayakan bayaran balik. / Cancellation must be made at least 48 hours before the booking date for refund eligibility.</p>
            <p>4. Kemudahan hendaklah digunakan mengikut peraturan dan garis panduan PBT. / The facility must be used in accordance with the PBT regulations and guidelines.</p>
            <p>5. Sebarang kerosakan pada kemudahan akan dikenakan kepada pengguna. / Any damage to the facility will be charged to the user.</p>
        </div>

        <!-- Official Stamp -->
        <div class="stamp">
            <div class="stamp-box">
                <p><strong>OFFICIAL STAMP</strong></p>
                <p style="margin-top: 40px;">{{ $pbt_name }}</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is a computer-generated invoice and does not require a signature.</p>
            <p>{{ $pbt_name }} | {{ $pbt_address }} | {{ $pbt_phone }}</p>
            <p>Powered by TAJDID Corporation Sdn Bhd</p>
            <p>Generated on {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
