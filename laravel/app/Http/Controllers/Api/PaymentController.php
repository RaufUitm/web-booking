<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\ToyyibpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    /**
     * Create a payment bill via ToyyibPay only
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        try {
            $booking = Booking::with(['facility', 'user'])->findOrFail($request->booking_id);

            if (strtolower($booking->status) === 'confirmed') {
                return response()->json(['error' => 'Booking already confirmed'], 400);
            }

            // Prefer stored total if available
            $totalAmount = floatval($booking->total_amount ?? 0);
            $pricePerHour = floatval($booking->facility->price_per_hour ?? 0);
            $pricePerDay = floatval($booking->facility->price_per_day ?? 0);
            if ($totalAmount <= 0) {
                if ($booking->booking_type === 'per_day') {
                    // Calculate number of days
                    $dayCount = 1;
                    if ($booking->end_date && $booking->booking_date) {
                        $start = strtotime($booking->booking_date);
                        $end = strtotime($booking->end_date);
                        $dayCount = max(1, round(($end - $start) / 86400) + 1);
                    }
                    $perDayPrice = $pricePerDay > 0 ? $pricePerDay : ($pricePerHour * 24);
                    $totalAmount = $perDayPrice * $dayCount;
                } else {
                    if ($booking->start_time && $booking->end_time) {
                        $start = strtotime('2000-01-01 ' . $booking->start_time);
                        $end = strtotime('2000-01-01 ' . $booking->end_time);
                        $hours = max(0, ($end - $start) / 3600);
                        $totalAmount = $pricePerHour * $hours;
                    }
                }
            }

            if ($totalAmount <= 0) {
                Log::warning('Computed totalAmount invalid', [
                    'booking_id' => $booking->id,
                    'type' => $booking->booking_type,
                    'price_per_hour' => $pricePerHour,
                    'price_per_day' => $pricePerDay,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                ]);
                return response()->json(['error' => 'Jumlah bayaran tidak sah untuk tempahan ini.'], 422);
            }

            if (empty($booking->total_amount) || $booking->total_amount != $totalAmount) {
                $booking->total_amount = $totalAmount;
                $booking->save();
            }

            if (!config('services.toyyibpay.enabled')) {
                return response()->json(['error' => 'ToyyibPay is not configured'], 500);
            }

            $toyyib = app(ToyyibpayService::class);
            $amountCents = max(0, intval(round($totalAmount * 100)));
            if ($amountCents < 100) {
                return response()->json(['error' => 'Jumlah pembayaran minimum ialah RM1.00'], 422);
            }

            $bill = $toyyib->createBill([
                'billName' => 'Tempahan ' . ($booking->facility->name ?? 'Kemudahan'),
                'billDescription' => 'Tempahan Ref: ' . ($booking->booking_reference ?? $booking->id),
                'billAmount' => $amountCents,
                'customerName' => $booking->user->name,
                'customerEmail' => $booking->user->email,
                'customerPhone' => $booking->user->phone ?? $booking->phone ?? '0123456789',
            ]);

            $billCode = $bill['billCode'] ?? null;
            $paymentUrl = $bill['payment_url'] ?? null;
            if (!$billCode || !$paymentUrl) {
                Log::error('ToyyibPay createBill invalid response', ['bill' => $bill]);
                return response()->json(['error' => 'Invalid payment response'], 500);
            }

            Payment::create([
                'booking_id' => $booking->id,
                'amount' => $totalAmount,
                'payment_method' => 'toyyibpay',
                'transaction_id' => $billCode,
                'payment_status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'payment_url' => $paymentUrl,
                'bill_code' => $billCode,
            ]);
        } catch (\Exception $e) {
            Log::error('Payment creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Failed to process payment',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle payment callback (ToyyibPay)
     */
    public function handleCallback(Request $request)
    {
        Log::info('ToyyibPay callback received', $request->all());

        try {
            if (!config('services.toyyibpay.enabled')) {
                return response()->json(['error' => 'ToyyibPay not configured'], 500);
            }

            $payload = $request->all();
            $billCode = $payload['billcode'] ?? $payload['bill_code'] ?? null;
            $statusId = $payload['status_id'] ?? $payload['status'] ?? null; // 1=success
            $payment = $billCode ? Payment::where('transaction_id', $billCode)->first() : null;
            if (!$payment) return response()->json(['error' => 'Payment not found'], 404);
            $booking = $payment->booking;

            if ($statusId == 1 || $statusId === '1') {
                $amount = $booking->total_amount ?: $payment->amount;
                $payment->update(['payment_status' => 'completed', 'paid_at' => now(), 'amount' => $amount]);
                if ($booking->status !== 'confirmed') $booking->update(['status' => 'confirmed']);
                try {
                    $invoiceService = app(\App\Services\InvoiceService::class);
                    $path = $invoiceService->getInvoicePath($booking) ?? $invoiceService->generateInvoice($booking);
                    Mail::to($booking->user->email)->send(new BookingConfirmation($booking, $path));
                } catch (\Exception $e) {
                    Log::error('Invoice/email failed', ['error' => $e->getMessage()]);
                }
            } else {
                $payment->update(['payment_status' => 'failed']);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Callback processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Callback processing failed'], 500);
        }
    }

    /**
     * Handle payment return URL (user redirect)
     */
    public function handleReturn(Request $request)
    {
        // ToyyibPay return handler
        $billCode = $request->input('billcode');
        $statusId = $request->input('status_id');

        // Find payment
        $payment = Payment::where('transaction_id', $billCode)->first();

        if (!$payment) {
            return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/payment/failed?error=payment_not_found');
        }

        $booking = $payment->booking;
        $district = strtolower($booking->facility->district);

        // Redirect based on status
        if ($statusId == 1) {
            // For local development, the external callback may not reach localhost.
            // Mark as completed here as a fallback (idempotent).
            if ($payment->payment_status !== 'completed') {
                try {
                    $amount = $booking->total_amount ?: $payment->amount;
                    $payment->update([
                        'payment_status' => 'completed',
                        'paid_at' => now(),
                        'amount' => $amount,
                    ]);
                    if ($booking->status !== 'confirmed') {
                        $booking->update(['status' => 'confirmed']);
                    }

                    // Generate invoice and send email (guarded to avoid duplicates)
                    $invoiceService = app(\App\Services\InvoiceService::class);
                    $path = $invoiceService->getInvoicePath($booking) ?? $invoiceService->generateInvoice($booking);
                    Mail::to($booking->user->email)->send(new BookingConfirmation($booking, $path));
                } catch (\Exception $e) {
                    Log::warning('Return handler could not finalize payment', ['error' => $e->getMessage()]);
                }
            }
            // Prefer reference if available, otherwise fallback to booking id
            $ref = $booking->booking_reference ?: $booking->id;
            return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/' . $district . '/payment/success?booking=' . $ref);
        } else {
            $ref = $booking->booking_reference ?: $booking->id;
            return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/' . $district . '/payment/failed?booking=' . $ref);
        }
    }

    /**
     * Verify payment status (for frontend polling)
     */
    public function verifyPayment(Request $request)
    {
        try {
            // Support lookup by reference or id (fallback)
            $ref = $request->input('booking_reference') ?? $request->input('reference') ?? $request->input('ref');
            $id = $request->input('id');

            $query = Booking::with(['payment', 'facility', 'user']);
            if ($ref) {
                if (preg_match('/^\d+$/', $ref)) {
                    $booking = $query->where('id', intval($ref))->first();
                } else {
                    $booking = $query->where('booking_reference', $ref)->first();
                }
            } elseif ($id) {
                $booking = $query->where('id', intval($id))->first();
            } else {
                return response()->json(['error' => 'Missing booking reference or id'], 422);
            }

            if (!$booking) {
                return response()->json(['error' => 'Booking not found'], 404);
            }

            return response()->json([
                'success' => true,
                'booking' => [
                    'id' => $booking->id,
                    'reference' => $booking->booking_reference,
                    'status' => $booking->status,
                    // expose computed payment status from relation for convenience
                    'payment_status' => optional($booking->payment)->payment_status,
                    'facility' => $booking->facility->name,
                    'booking_date' => $booking->booking_date,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'total_amount' => $booking->total_amount,
                ],
                'payment' => $booking->payment ? [
                    'payment_status' => $booking->payment->payment_status,
                    'payment_method' => $booking->payment->payment_method,
                    'transaction_id' => $booking->payment->transaction_id,
                    'paid_at' => $booking->payment->paid_at,
                ] : null,
            ]);

        } catch (\Exception $e) {
            Log::error('Payment verification error', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Verification failed'], 500);
        }
    }

    /**
     * Manual payment verification via toyyibPay API
     */
    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'bill_code' => 'required|string',
        ]);

        try {
            $billCode = $request->bill_code;
            $secretKey = config('services.toyyibpay.secret_key');
            $baseUrl = rtrim(config('services.toyyibpay.base_url'), '/');

            // Call toyyibPay API to get bill transactions
            $response = Http::asForm()->post($baseUrl . '/index.php/api/getBillTransactions', [
                'billCode' => $billCode,
                'userSecretKey' => $secretKey,
            ]);

            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to check payment status'], 500);
            }

            $transactions = $response->json();

            return response()->json([
                'success' => true,
                'transactions' => $transactions,
            ]);

        } catch (\Exception $e) {
            Log::error('Manual payment check error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Status check failed'], 500);
        }
    }

    /**
     * Download invoice PDF for a booking (owner or admin only)
     */
    public function downloadInvoice(Request $request, $id)
    {
        try {
            $booking = Booking::with(['user', 'facility.category', 'payment'])->find($id);

            if (!$booking) {
                return response()->json(['error' => 'Booking not found'], 404);
            }

            $user = $request->user();
            $isAdmin = in_array($user->role, ['admin', 'district_admin', 'state_admin', 'master_admin']);
            $isOwner = $booking->user_id === $user->id;

            if (!$isAdmin && !$isOwner) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Only allow invoice if payment completed
            if (!$booking->payment || $booking->payment->payment_status !== 'completed') {
                return response()->json(['error' => 'Invoice not available until payment is completed'], 400);
            }

            $invoiceService = app(\App\Services\InvoiceService::class);
            
            // Always regenerate invoice to ensure it's up to date with district styling
            $path = $invoiceService->generateInvoice($booking);

            $absolute = storage_path('app/' . $path);
            if (!file_exists($absolute)) {
                return response()->json(['error' => 'Invoice file not found'], 404);
            }

            // Return as inline PDF for viewing in browser
            return response()->file($absolute, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="invoice-' . ($booking->booking_reference ?: $booking->id) . '.pdf"'
            ]);
        } catch (\Exception $e) {
            \Log::error('Invoice download failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Failed to download invoice: ' . $e->getMessage()], 500);
        }
    }
}
