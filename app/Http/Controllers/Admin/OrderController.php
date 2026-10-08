<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WalletTransaction;
use App\Notifications\OrderUpdateNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with('user')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = trim((string) $request->input('q'));
                $term = '%'.$search.'%';
                $query->where(function ($query) use ($search, $term): void {
                    if (ctype_digit($search)) {
                        $query->where('id', (int) $search)
                            ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term));
                    } else {
                        $query->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term));
                    }
                });
            })
            ->when(in_array($request->query('status'), ['awaiting_payment', 'received', 'preparing', 'ready', 'completed', 'cancelled'], true), fn ($query) => $query->where('status', $request->query('status')))
            ->when(in_array($request->query('refund_status'), ['none', 'pending', 'refunded'], true), fn ($query) => $query->where('refund_status', $request->query('refund_status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items.vendor', 'cancelledBy', 'refundedBy']);

        return view('admin.orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $order, $data): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (in_array($lockedOrder->status, ['cancelled', 'completed'], true)) {
                throw ValidationException::withMessages(['order' => 'This order is already complete or cancelled.']);
            }

            $isPaid = $lockedOrder->payment_status === 'paid';
            $isWalletPayment = $lockedOrder->payment_method === 'wallet';

            $lockedOrder->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $request->user()->id,
                'cancellation_reason' => $data['cancellation_reason'] ?? null,
                'refund_status' => $isPaid ? ($isWalletPayment ? 'refunded' : 'pending') : 'none',
                'refund_amount' => $isPaid ? $lockedOrder->total : null,
                'refund_reference' => null,
                'refunded_at' => $isPaid && $isWalletPayment ? now() : null,
                'refunded_by' => $isPaid && $isWalletPayment ? $request->user()->id : null,
            ]);

            if ($isPaid && $isWalletPayment) {
                $customer = $lockedOrder->user()->lockForUpdate()->firstOrFail();
                $reference = 'atu-refund-order-'.$lockedOrder->id;

                if (! WalletTransaction::where('reference', $reference)->exists()) {
                    $customer->increment('wallet_balance', $lockedOrder->total);
                    $customer->walletTransactions()->create([
                        'type' => 'credit',
                        'amount' => $lockedOrder->total,
                        'status' => 'completed',
                        'reference' => $reference,
                        'description' => 'Refund for cancelled order #'.$lockedOrder->id,
                        'completed_at' => now(),
                    ]);
                }
            }
        });

        $order->refresh();
        $order->user->notify(new OrderUpdateNotification(
            $order,
            $order->refund_status === 'pending'
                ? 'Your order was cancelled. Your Paystack refund is waiting to be processed.'
                : ($order->refund_status === 'refunded'
                    ? 'Your order was cancelled and the refund was returned to your ATU Eats wallet.'
                    : 'Your order was cancelled before payment was completed.'),
        ));
        OrderStatusUpdated::dispatch($order->id);

        return back()->with('success', 'Order cancelled. Refund status: '.str_replace('_', ' ', $order->refund_status).'.');
    }

    public function recordRefund(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'refund_reference' => ['required', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($request, $order, $data): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->status !== 'cancelled'
                || $lockedOrder->payment_status !== 'paid'
                || $lockedOrder->payment_method !== 'paystack'
                || $lockedOrder->refund_status !== 'pending') {
                throw ValidationException::withMessages(['order' => 'Only a cancelled, paid Paystack order awaiting a refund can be marked refunded.']);
            }

            $lockedOrder->update([
                'refund_status' => 'refunded',
                'refund_reference' => $data['refund_reference'],
                'refunded_at' => now(),
                'refunded_by' => $request->user()->id,
            ]);
        });

        $order->refresh();
        $order->user->notify(new OrderUpdateNotification($order, 'Your Paystack refund for cancelled order #'.$order->id.' has been processed.'));
        OrderStatusUpdated::dispatch($order->id);

        return back()->with('success', 'Refund marked as completed.');
    }
}
