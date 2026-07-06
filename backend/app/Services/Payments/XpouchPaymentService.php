<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XpouchPaymentService
{
    public function initiate(Payment $payment, array $customer): array
    {
        $this->assertConfigured();

        $payload = [
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'reference' => $payment->reference,
            'description' => $this->descriptionFor($payment),
            'customer' => $customer,
            'callback_url' => config('services.xpouch.callback_url'),
            'webhook_url' => config('services.xpouch.webhook_url'),
            'metadata' => [
                'payment_id' => $payment->id,
                'purpose' => $payment->purpose,
                'payable_type' => $payment->payable_type,
                'payable_id' => $payment->payable_id,
            ],
        ];

        $response = $this->post(config('services.xpouch.initialize_path', '/payments/initialize'), $payload);

        Log::info('xPouch payment initiated', [
            'payment_id' => $payment->id,
            'reference' => $payment->reference,
        ]);

        return $response;
    }

    public function verify(string $reference): array
    {
        $this->assertConfigured();

        $path = str_replace('{reference}', urlencode($reference), config('services.xpouch.verify_path', '/payments/verify/{reference}'));

        return $this->get($path);
    }

    public function extractCheckoutUrl(array $response): ?string
    {
        return data_get($response, 'data.checkout_url')
            ?? data_get($response, 'data.authorization_url')
            ?? data_get($response, 'data.payment_url')
            ?? data_get($response, 'checkout_url')
            ?? data_get($response, 'payment_url');
    }

    public function extractProviderReference(array $response): ?string
    {
        return data_get($response, 'data.reference')
            ?? data_get($response, 'data.transaction_reference')
            ?? data_get($response, 'reference')
            ?? data_get($response, 'transaction_reference');
    }

    public function isSuccessfulPayload(array $payload): bool
    {
        $status = strtolower((string) (
            data_get($payload, 'data.status')
            ?? data_get($payload, 'status')
            ?? data_get($payload, 'event')
            ?? ''
        ));

        return in_array($status, ['success', 'successful', 'completed', 'paid', 'payment.successful', 'payment.received'], true);
    }

    private function descriptionFor(Payment $payment): string
    {
        return match ($payment->purpose) {
            'membership_renewal' => 'NIMN membership renewal',
            'publication_purchase' => 'NIMN publication purchase',
            'wallet_deposit' => 'NIMN wallet deposit',
            default => 'NIMN payment',
        };
    }

    private function assertConfigured(): void
    {
        if (! config('services.xpouch.api_key') || ! config('services.xpouch.api_secret')) {
            throw new \RuntimeException('xPouch API credentials are not configured.');
        }
    }

    private function headers(): array
    {
        return [
            'X-API-Key' => trim((string) config('services.xpouch.api_key')),
            'X-API-Secret' => trim((string) config('services.xpouch.api_secret')),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    private function post(string $path, array $payload): array
    {
        $response = Http::withHeaders($this->headers())
            ->asJson()
            ->post(rtrim((string) config('services.xpouch.base_url'), '/') . $path, $payload);

        if (! $response->successful()) {
            throw new RequestException($response);
        }

        return $response->json() ?? [];
    }

    private function get(string $path): array
    {
        $response = Http::withHeaders($this->headers())
            ->acceptJson()
            ->get(rtrim((string) config('services.xpouch.base_url'), '/') . $path);

        if (! $response->successful()) {
            throw new RequestException($response);
        }

        return $response->json() ?? [];
    }
}
