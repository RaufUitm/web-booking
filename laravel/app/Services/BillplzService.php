<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BillplzService
{
    private string $apiKey;
    private string $apiUrl;
    private ?string $collectionId;

    public function __construct()
    {
        $cfg = config('services.billplz');
        $this->apiKey = (string) ($cfg['api_key'] ?? '');
        $this->apiUrl = (string) ($cfg['api_url'] ?? '');
        $this->collectionId = $cfg['collection_id'] ?? null;
    }

    public function createBill(array $data): array
    {
        $payload = [
            'collection_id' => $this->collectionId,
            'email' => $data['email'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'name' => $data['name'] ?? null,
            'amount' => (int) $data['amount'], // in cents
            'callback_url' => $data['callback_url'],
            'redirect_url' => $data['redirect_url'],
            'reference_1' => $data['reference_1'] ?? null,
            'description' => $data['description'] ?? null,
        ];

        \Log::info('Billplz createBill payload', $payload);

        $response = Http::withBasicAuth($this->apiKey, '')
            ->asForm()
            ->post($this->apiUrl . '/bills', $payload);

        if (!$response->successful()) {
            \Log::error('Billplz createBill failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \RuntimeException('Billplz createBill failed: ' . $response->body());
        }

        $result = $response->json();
        \Log::info('Billplz createBill response', $result);

        return $result;
    }

    public static function verifySignature(string $xSignature, array $payload): bool
    {
        $secret = (string) (config('services.billplz.x_signature') ?? '');
        if (!$secret) return false;

        // Billplz signature: HMAC SHA256 of ordered parameters
        // Common payload keys include: id, paid_at, paid, transaction_id, amount, etc.
        // We'll sign based on raw body (recommended) if available; here reconstruct minimal set.
        $keys = ['id', 'paid', 'paid_at', 'amount', 'transaction_id'];
        $signedString = '';
        foreach ($keys as $k) {
            if (array_key_exists($k, $payload)) {
                $signedString .= $k . '|' . (string)$payload[$k];
            }
        }
        $hash = hash_hmac('sha256', $signedString, $secret);
        return hash_equals($hash, $xSignature);
    }
}
