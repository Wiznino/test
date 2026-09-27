<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WalletTransaction;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PaystackService
{
    /** @return array{authorization_url: string, reference: string} */
    public function initialize(Order $order): array
    {
        $secretKey = config('services.paystack.secret_key');

        if (! is_string($secretKey) || $secretKey === '') {
            throw new RuntimeException('Online payments are not configured yet. Please contact the cafeteria.');
        }

        $reference = 'atu-'.$order->id.'-'.Str::uuid();
        $order->update(['payment_reference' => $reference]);

        return $this->initializePayment($order->user->email, (float) $order->total, $reference, ['order_id' => $order->id]);
    }

    /** @return array{authorization_url: string, reference: string} */
    public function initializeWalletTopUp(WalletTransaction $transaction): array
    {
        $reference = 'atu-wallet-'.$transaction->id.'-'.Str::uuid();
        $transaction->update(['reference' => $reference]);

        return $this->initializePayment($transaction->user->email, (float) $transaction->amount, $reference, ['wallet_transaction_id' => $transaction->id]);
    }

    /** @param array<string, int> $metadata
     * @return array{authorization_url: string, reference: string}
     */
    private function initializePayment(string $email, float $amount, string $reference, array $metadata): array
    {
        $secretKey = config('services.paystack.secret_key');

        if (! is_string($secretKey) || $secretKey === '') {
            throw new RuntimeException('Online payments are not configured yet. Please contact the cafeteria.');
        }

        try {
            $response = Http::acceptJson()
                ->withToken($secretKey)
                ->withOptions(['verify' => config('services.paystack.ca_bundle', true)])
                ->connectTimeout(3)
                ->timeout(10)
                ->post('https://api.paystack.co/transaction/initialize', [
                    'email' => $email,
                    'amount' => (int) round($amount * 100),
                    'currency' => 'GHS',
                    'reference' => $reference,
                    'callback_url' => route('payments.paystack.callback'),
                    'metadata' => $metadata,
                ]);
        } catch (ConnectionException $exception) {
            report($exception);

            throw new RuntimeException('Paystack could not be reached. Please try again.', previous: $exception);
        }

        if (! $response->successful() || ! $response->json('status') || ! $response->json('data.authorization_url')) {
            throw new RuntimeException('Paystack could not start this payment. Please try again.');
        }

        return [
            'authorization_url' => $response->json('data.authorization_url'),
            'reference' => $reference,
        ];
    }

    /** @return array<string, mixed> */
    public function verify(string $reference): array
    {
        $secretKey = config('services.paystack.secret_key');

        if (! is_string($secretKey) || $secretKey === '') {
            throw new RuntimeException('Online payments are not configured yet.');
        }

        try {
            $response = Http::acceptJson()
                ->withToken($secretKey)
                ->withOptions(['verify' => config('services.paystack.ca_bundle', true)])
                ->connectTimeout(3)
                ->timeout(10)
                ->get('https://api.paystack.co/transaction/verify/'.rawurlencode($reference));
        } catch (ConnectionException $exception) {
            report($exception);

            throw new RuntimeException('Payment verification is temporarily unavailable.', previous: $exception);
        }

        if (! $response->successful() || ! $response->json('status')) {
            throw new RuntimeException('Payment verification is temporarily unavailable.');
        }

        return $response->json('data', []);
    }

    public function hasValidWebhookSignature(string $payload, ?string $signature): bool
    {
        $secretKey = config('services.paystack.secret_key');

        if (! is_string($secretKey) || $secretKey === '' || ! is_string($signature)) {
            return false;
        }

        return hash_equals(hash_hmac('sha512', $payload, $secretKey), $signature);
    }
}
