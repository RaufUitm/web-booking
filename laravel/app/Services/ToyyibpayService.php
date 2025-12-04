<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ToyyibpayService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('services.toyyibpay');
    }

    public function createBill(array $payload): array
    {
        if (!($this->config['enabled'] ?? false)) {
            throw new \RuntimeException('ToyyibPay is not enabled. Missing secret key.');
        }

        $endpoint = rtrim($this->config['base_url'], '/') . '/index.php/api/createBill';

        $amountCents = (int) ($payload['billAmount'] ?? 0);
        if ($amountCents <= 0) {
            throw new \InvalidArgumentException('Invalid bill amount for ToyyibPay');
        }

        // Enforce ToyyibPay field length constraints (per API reference)
        $billName = Str::limit((string)($payload['billName'] ?? 'Tempahan'), 30, '');
        $billDescription = Str::limit((string)($payload['billDescription'] ?? 'Bayaran tempahan'), 200, '');
        $billTo = Str::limit((string)($payload['customerName'] ?? 'Pelanggan'), 100, '');

        $data = [
            'userSecretKey' => $this->config['secret_key'],
            'categoryCode' => $this->config['category_code'],
            'billName' => $billName,
            'billDescription' => $billDescription,
            'billAmount' => (string) $amountCents, 
            'billPriceSetting' => 1, // fixed price set by merchant
            'billPayorInfo' => 1, // include payer info
            'billReturnUrl' => $this->config['return_url'],
            'billCallbackUrl' => $this->config['callback_url'],
            'billTo' => $billTo,
            'billEmail' => $payload['customerEmail'] ?? null,
            'billPhone' => $payload['customerPhone'] ?? null,
            'billExpiryDate' => $payload['billExpiryDate'] ?? null,
            'billChargeToCustomer' => 0,
        ];

        try {
            $response = Http::asForm()->post($endpoint, $data);
            $status = $response->status();
            $body = $response->body();

            if ($response->failed()) {
                Log::error('ToyyibPay createBill failed', [
                    'status' => $status,
                    'body' => $body,
                    'endpoint' => $endpoint,
                ]);
                throw new \RuntimeException('ToyyibPay API error: HTTP ' . $status);
            }

            $decoded = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('ToyyibPay createBill non-JSON response', [
                    'status' => $status,
                    'body' => $body,
                ]);
                throw new \RuntimeException('ToyyibPay returned an unexpected response');
            }

            $bill = is_array($decoded) ? ($decoded[0] ?? $decoded) : $decoded;

            if (isset($bill['BillCode']) && $bill['BillCode']) {
                return [
                    'billCode' => $bill['BillCode'],
                    'payment_url' => rtrim($this->config['base_url'], '/') . '/' . $bill['BillCode'],
                    'raw' => $bill,
                ];
            }

            $apiMsg = $bill['msg'] ?? $bill['message'] ?? $bill['Message'] ?? $bill['error'] ?? $bill['error_desc'] ?? null;
            Log::error('ToyyibPay createBill missing BillCode', [
                'status' => $status,
                'response' => $decoded,
            ]);
            throw new \RuntimeException('ToyyibPay error: ' . ($apiMsg ?: 'Invalid payment response'));
        } catch (\Throwable $e) {
            Log::error('ToyyibPay createBill exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function verifyCallback(array $data): array
    {
        // ToyyibPay callback fields: billcode, order_id, status, amount, refno
        return [
            'billcode' => $data['billcode'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'status' => $data['status'] ?? null, // 1=success, 2=pending, 3=failed
            'amount' => $data['amount'] ?? null,
            'refno' => $data['refno'] ?? null,
        ];
    }
}
