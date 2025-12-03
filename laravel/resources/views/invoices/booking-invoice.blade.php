<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $booking->booking_reference }}</title>
    <style>
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
            border-bottom: 3px solid #FF8C00;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #FF8C00;
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
            color: #FF8C00;
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
        }

        .booking-details h3 {
            background-color: #FF8C00;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
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
            color: #FF8C00;
            padding-top: 10px;
            border-top: 2px solid #ddd;
        }

        .payment-info {
            background-color: #e8f5e9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 20px 0;
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
            border: 2px solid #FF8C00;
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
            <h1>INVOIS / INVOICE</h1>
            <h2>{{ $pbt_name }}</h2>
        </div>

        <!-- Invoice Info -->
        <div class="invoice-info">
            <div class="invoice-info-left">
                <div class="info-block">
                    <h3>PBT Information</h3>
                    <p><strong>{{ $pbt_name }}</strong></p>
                    <p>{{ $pbt_address }}</p>
                    <p>Tel: {{ $pbt_phone }}</p>
                    <p>Email: {{ $pbt_email }}</p>
                </div>

                <div class="info-block">
                    <h3>Bill To</h3>
                    <p><strong>{{ $booking->user->name }}</strong></p>
                    <p>{{ $booking->user->email }}</p>
                    <p>{{ $booking->phone ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="invoice-info-right">
                <div class="info-block">
                    <p><strong>Invoice Number:</strong></p>
                    <p style="font-size: 16px; color: #FF8C00;">{{ $invoice_number }}</p>
                </div>
                <div class="info-block">
                    <p><strong>Invoice Date:</strong></p>
                    <p>{{ $invoice_date }}</p>
                </div>
                <div class="info-block">
                    <p><strong>Booking Reference:</strong></p>
                    <p>{{ $booking->booking_reference }}</p>
                </div>
            </div>
        </div>

        <!-- Booking Details -->
        <div class="booking-details">
            <h3>BOOKING DETAILS</h3>
            <table class="details-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="width: 30%;">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Facility Name</td>
                        <td><strong>{{ $booking->facility->name }}</strong></td>
                    </tr>
                    <tr>
                        <td>Category</td>
                        <td>{{ $booking->facility->category->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Booking Date</td>
                        <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y (l)') }}</td>
                    </tr>
                    <tr>
                        <td>Time Slot</td>
                        <td>
                            @if($booking->booking_type === 'per_day')
                                Full Day / Sepanjang Hari
                            @else
                                {{ $booking->start_time }} - {{ $booking->end_time }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Booking Type</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $booking->booking_type)) }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td><strong style="color: #4CAF50;">{{ $booking->status }}</strong></td>
                    </tr>
                </tbody>
            </table>

            <!-- Amount Section -->
            <div class="amount-section">
                <div class="amount-row">
                    <span>Subtotal: </span>
                    <strong>RM {{ number_format($booking->total_amount, 2) }}</strong>
                </div>
                <div class="amount-row">
                    <span>Tax (0%): </span>
                    <strong>RM 0.00</strong>
                </div>
                <div class="amount-row total">
                    <span>TOTAL AMOUNT: </span>
                    <strong>RM {{ number_format($booking->total_amount, 2) }}</strong>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        @if($booking->payment)
        <div class="payment-info">
            <h3>✓ PAYMENT CONFIRMED</h3>
            <table style="width: 100%;">
                <tr>
                    <td><strong>Payment Method:</strong></td>
                    <td>{{ strtoupper($booking->payment->payment_method) }}</td>
                </tr>
                <tr>
                    <td><strong>Transaction ID:</strong></td>
                    <td>{{ $booking->payment->payment_reference ?? $booking->payment->transaction_id }}</td>
                </tr>
                <tr>
                    <td><strong>Payment Date:</strong></td>
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
            <h4>Terms & Conditions:</h4>
            <p>1. This invoice serves as an official receipt for your facility booking.</p>
            <p>2. Booking confirmation is subject to payment clearance.</p>
            <p>3. Cancellation must be made at least 48 hours before the booking date for refund eligibility.</p>
            <p>4. The facility must be used in accordance with the PBT regulations and guidelines.</p>
            <p>5. Any damage to the facility will be charged to the user.</p>
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
            <p>Generated on {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
