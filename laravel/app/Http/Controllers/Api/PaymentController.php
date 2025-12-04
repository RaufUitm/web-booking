<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmation;

class PaymentController extends Controller
{
    /**
     * Create a payment bill via toyyibPay
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        try {
            $booking = Booking::with(['facility', 'user'])->findOrFail($request->booking_id);

            // Check if booking already paid
            if ($booking->status === 'CONFIRMED') {
                return response()->json([
                    'error' => 'Booking already confirmed'
                ], 400);
            }

            // Calculate total amount
            $totalAmount = 0;
            $pricePerHour = floatval($booking->facility->price_per_hour ?? 0);
            $pricePerDay = floatval($booking->facility->price_per_day ?? 0);
            
            if ($booking->booking_type === 'per_day') {
                $totalAmount = $pricePerDay > 0 ? $pricePerDay : ($pricePerHour * 24);
            } else {
                // Calculate hours from start_time to end_time
                if ($booking->start_time && $booking->end_time) {
                    $start = strtotime($booking->start_time);
                    $end = strtotime($booking->end_time);
                    $hours = ($end - $start) / 3600;
                    $totalAmount = $pricePerHour * $hours;
                }
            }

            // Use Billplz gateway
            if (config('services.billplz.enabled')) {
                $billplz = app(\App\Services\BillplzService::class);
                $amountCents = max(0, intval($totalAmount * 100));
                if ($amountCents < 100) {
                    // Billplz requires minimum amount of RM1.00 (100 cents)
                    return response()->json(['error' => 'Jumlah pembayaran minimum ialah RM1.00'], 422);
                }
                $bill = $billplz->createBill([
                    'email' => $booking->user->email,
                    'mobile' => $booking->user->phone ?? $booking->phone ?? '0123456789',
                    'name' => $booking->user->name,
                    'amount' => $amountCents,
                    'callback_url' => config('app.url') . '/api/payment/callback',
                    'redirect_url' => config('app.url') . '/api/payment/return',
                    'reference_1' => $booking->booking_reference,
                    'description' => 'Facility Booking - ' . $booking->facility->district . ' - Ref: ' . $booking->booking_reference,
                ]);

                $billId = $bill['id'] ?? null;
                $paymentUrl = $bill['url'] ?? null;
                if (!$billId || !$paymentUrl) {
                    Log::error('Billplz createBill invalid response', ['bill' => $bill]);
                    return response()->json(['error' => 'Invalid payment response'], 500);
                }

                Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $totalAmount,
                    'payment_method' => 'online_banking',
                    'transaction_id' => $billId,
                    'payment_status' => 'pending',
                ]);

                return response()->json([
                    'success' => true,
                    'payment_url' => $paymentUrl,
                    'bill_code' => $billId,
                ]);
            } else {
                return response()->json(['error' => 'Billplz is not configured'], 500);
            }

        } catch (\Exception $e) {
            Log::error('Payment creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Bubble up more helpful message to frontend while keeping 500
            return response()->json([
                'error' => 'Failed to process payment',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment callback from toyyibPay
     */
    public function handleCallback(Request $request)
    {
        Log::info('Payment callback received', $request->all());

        try {
            if (config('services.billplz.enabled')) {
                // Billplz callback
                $xSignature = $request->header('x-signature') ?? '';
                $payload = $request->all();
                if (!\App\Services\BillplzService::verifySignature($xSignature, $payload)) {
                    return response()->json(['error' => 'Invalid signature'], 403);
                }

                $billId = $payload['id'] ?? null;
                $paid = filter_var($payload['paid'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $payment = Payment::where('transaction_id', $billId)->first();
                if (!$payment) return response()->json(['error' => 'Payment not found'], 404);
                $booking = $payment->booking;

                if ($paid) {
                    $payment->update(['payment_status' => 'completed', 'paid_at' => now()]);
                    if ($booking->status !== 'confirmed') $booking->update(['status' => 'confirmed']);
                    // Invoice + email
                    try {
                        $invoiceService = app(\App\Services\InvoiceService::class);
                        $path = $invoiceService->getInvoicePath($booking) ?? $invoiceService->generateInvoice($booking);
                        Mail::to($booking->user->email)->send(new BookingConfirmation($booking, $path));
                    } catch (\Exception $e) { Log::error('Invoice/email failed', ['error' => $e->getMessage()]); }
                } else {
                    $payment->update(['payment_status' => 'failed']);
                }

                return response()->json(['success' => true]);
            }

                // No legacy gateway; only Billplz supported
                return response()->json(['error' => 'Gateway not configured'], 500);

        } catch (\Exception $e) {
            Log::error('Callback processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Callback processing failed'], 500);
        }
    }

    /**
     * Handle payment return URL (user redirect)
     */
    public function handleReturn(Request $request)
    {
        // Billplz return uses GET redirect to our return URL with status in query sometimes, otherwise rely on callback
        if (config('services.billplz.enabled')) {
            // Billplz return sends nested query params: billplz[id], billplz[paid], billplz[paid_at]
            $billId = $request->input('bill_id')
                ?? $request->input('billplz.id')
                ?? $request->input('id');
            $paid = $request->input('billplz.paid') ?? $request->input('paid');
            $payment = $billId ? Payment::where('transaction_id', $billId)->first() : null;
            if (!$payment) {
                return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/payment/failed?error=payment_not_found');
            }
            $booking = $payment->booking;
            $district = strtolower($booking->facility->district);
            if ($paid === 'true' || $payment->payment_status === 'completed') {
                // Fallback finalize (local dev)
                if ($payment->payment_status !== 'completed') {
                    $payment->update(['payment_status' => 'completed', 'paid_at' => now()]);
                    if ($booking->status !== 'confirmed') $booking->update(['status' => 'confirmed']);
                }
                $ref = $booking->booking_reference ?: $booking->id;
                return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/' . $district . '/payment/success?booking=' . $ref);
            } else {
                $ref = $booking->booking_reference ?: $booking->id;
                return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/' . $district . '/payment/failed?booking=' . $ref);
            }
        }

        // No legacy return handler; Billplz handled above
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
                    $payment->update([
                        'payment_status' => 'completed',
                        'paid_at' => now(),
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
            $apiUrl = config('services.toyyibpay.api_url');

            // Call toyyibPay API to get bill transactions
            $response = Http::asForm()->post($apiUrl . '/index.php/api/getBillTransactions', [
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
