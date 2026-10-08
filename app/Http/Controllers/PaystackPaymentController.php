<?php

namespace App\Http\Controllers;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\WalletTransaction;
use App\Notifications\OrderUpdateNotification;
use App\Notifications\WalletTopUpNotification;
use App\Services\PaystackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaystackPaymentController extends Controller
{
    public function callback(Request $request, PaystackService $paystack): RedirectResponse
    {
        $reference = $request->validate(['reference' => ['required', 'string', 'max:100']])['reference'];
        $walletTransaction = WalletTransaction::where('reference', $reference)->first();
        if ($walletTransaction) {
            abort_unless($walletTransaction->user_id === $request->user()->id, 404);

            try {
                $this->markWalletTopUpSuccessful($walletTransaction, $paystack->verify($reference));
            } catch (RuntimeException $exception) {
                report($exception);

                return redirect()->route('wallet.show')->with('error', $exception->getMessage());
            }

            $walletTransaction->refresh();

            return $walletTransaction->status === 'completed'
                ? redirect()->route('wallet.show')->with('success', 'Top up received. Your wallet balance is ready to use.')
                : redirect()->route('wallet.show')->with('error', 'Paystack has not confirmed this top up yet. You can retry it from your wallet.');
        }

        $order = Order::where('payment_reference', $reference)->firstOrFail();
        abort_unless($order->user_id === $request->user()->id, 404);

        try {
            $transaction = $paystack->verify($reference);
            $this->markSuccessful($order, $transaction);
        } catch (RuntimeException $exception) {
            report($exception);

            return redirect()->route('orders.show', $order)->with('error', $exception->getMessage());
        }

        $order->refresh();

        return $order->payment_status === 'paid'
            ? redirect()->route('orders.show', $order)->with('success', $order->status === 'cancelled'
                ? 'Payment was received after cancellation. Your refund is now awaiting processing.'
                : 'Payment received. Your cafeteria order is confirmed.')
            : redirect()->route('orders.show', $order)->with('error', 'Payment has not been confirmed yet. Refresh this page in a moment.');
    }

    public function webhook(Request $request, PaystackService $paystack): JsonResponse
    {
        abort_unless($paystack->hasValidWebhookSignature($request->getContent(), $request->header('x-paystack-signature')), 401);

        $payload = $request->json()->all();
        if (($payload['event'] ?? null) !== 'charge.success' || ! is_string($payload['data']['reference'] ?? null)) {
            return response()->json(['received' => true]);
        }

        $walletTransaction = WalletTransaction::where('reference', $payload['data']['reference'])->first();
        if ($walletTransaction) {
            try {
                $transaction = $paystack->verify($walletTransaction->reference);
                $this->markWalletTopUpSuccessful($walletTransaction, $transaction);
            } catch (RuntimeException $exception) {
                report($exception);

                return response()->json(['message' => 'Payment verification failed.'], 503);
            }

            return response()->json(['received' => true]);
        }

        $order = Order::where('payment_reference', $payload['data']['reference'])->first();
        if (! $order) {
            return response()->json(['received' => true]);
        }

        try {
            $transaction = $paystack->verify($order->payment_reference);
            $this->markSuccessful($order, $transaction);
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json(['message' => 'Payment verification failed.'], 503);
        }

        return response()->json(['received' => true]);
    }

    public function retry(Request $request, Order $order, PaystackService $paystack): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        abort_unless($order->payment_status === 'pending' && $order->status !== 'cancelled', 409);

        try {
            $payment = $paystack->initialize($order);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->away($payment['authorization_url']);
    }

    /** @param array<string, mixed> $transaction */
    private function markSuccessful(Order $order, array $transaction): void
    {
        $expectedAmount = (int) round(((float) $order->total) * 100);
        if (($transaction['status'] ?? null) !== 'success'
            || ($transaction['reference'] ?? null) !== $order->payment_reference
            || ($transaction['currency'] ?? null) !== 'GHS'
            || (int) ($transaction['amount'] ?? 0) !== $expectedAmount) {
            return;
        }

        $wasPaid = DB::transaction(function () use ($order): bool {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->payment_status === 'paid') {
                return false;
            }

            $wasCancelled = $lockedOrder->status === 'cancelled';
            $lockedOrder->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'status' => $wasCancelled ? 'cancelled' : 'received',
                'refund_status' => $wasCancelled ? 'pending' : $lockedOrder->refund_status,
                'refund_amount' => $wasCancelled ? $lockedOrder->total : $lockedOrder->refund_amount,
            ]);

            return true;
        });

        if ($wasPaid) {
            $order->refresh();
            $order->user->notify(new OrderUpdateNotification(
                $order,
                $order->status === 'cancelled'
                    ? 'Payment was received after cancellation. Your refund is awaiting processing.'
                    : 'Payment received. The cafeteria has your order.',
            ));
            OrderStatusUpdated::dispatch($order->id);
        }
    }

    /** @param array<string, mixed> $transaction */
    private function markWalletTopUpSuccessful(WalletTransaction $walletTransaction, array $transaction): void
    {
        $expectedAmount = (int) round(((float) $walletTransaction->amount) * 100);
        if (($transaction['status'] ?? null) !== 'success'
            || ($transaction['reference'] ?? null) !== $walletTransaction->reference
            || ($transaction['currency'] ?? null) !== 'GHS'
            || (int) ($transaction['amount'] ?? 0) !== $expectedAmount) {
            return;
        }

        $wasCredited = DB::transaction(function () use ($walletTransaction): bool {
            $lockedTransaction = WalletTransaction::query()->lockForUpdate()->findOrFail($walletTransaction->id);
            if ($lockedTransaction->status !== 'pending') {
                return false;
            }

            $user = $lockedTransaction->user()->lockForUpdate()->firstOrFail();
            $user->increment('wallet_balance', $lockedTransaction->amount);
            $lockedTransaction->update(['status' => 'completed', 'completed_at' => now()]);

            return true;
        });

        if ($wasCredited) {
            $walletTransaction->refresh();
            $walletTransaction->user->notify(new WalletTopUpNotification($walletTransaction));
        }
    }
}
