<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use App\Services\PaystackService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;

class WalletController extends Controller
{
    public function show(): View
    {
        $transactions = Auth::user()->walletTransactions()->latest()->paginate(12);

        return view('wallet.show', compact('transactions'));
    }

    public function storeTopUp(Request $request, PaystackService $paystack): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:1', 'max:5000'],
        ]);

        if (! config('services.paystack.secret_key')) {
            return back()->withInput()->with('error', 'Online payments are not configured yet. Please contact the cafeteria.');
        }

        $transaction = $request->user()->walletTransactions()->create([
            'type' => 'credit',
            'amount' => $data['amount'],
            'status' => 'pending',
            'reference' => 'pending-'.Str::uuid(),
            'description' => 'Wallet top up',
        ]);

        try {
            $payment = $paystack->initializeWalletTopUp($transaction);
        } catch (RuntimeException $exception) {
            report($exception);

            return redirect()->route('wallet.show')->with('error', $exception->getMessage());
        }

        return redirect()->away($payment['authorization_url']);
    }

    public function retryTopUp(Request $request, WalletTransaction $walletTransaction, PaystackService $paystack): RedirectResponse
    {
        abort_unless($walletTransaction->user_id === $request->user()->id, 404);
        abort_unless($walletTransaction->type === 'credit' && $walletTransaction->status === 'pending', 409);

        try {
            $payment = $paystack->initializeWalletTopUp($walletTransaction);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->away($payment['authorization_url']);
    }
}
