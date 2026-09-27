@extends('layouts.app')
@section('title', 'Your wallet')
@section('content')
<section class="page-shell wallet-shell">
    <div class="page-kicker"><a href="{{ route('dashboard') }}">← Your account</a><span>ATU EATS WALLET</span></div>
    <div class="wallet-hero">
        <div><span class="eyebrow">AVAILABLE BALANCE</span><h1>GHS {{ number_format((float) Auth::user()->wallet_balance, 2) }}</h1><p>Add money to your wallet, then use your balance to pay for cafeteria orders.</p></div>
        <span class="wallet-mark" aria-hidden="true">₵</span>
    </div>
    <div class="wallet-content-grid">
        <section class="summary-card wallet-topup-card">
            <span class="eyebrow">ADD MONEY</span><h2>Top up your wallet</h2>
            <p>Choose an amount from GHS 1 to GHS 5,000. Pay securely with Paystack.</p>
            <form method="POST" action="{{ route('wallet.topups.store') }}" class="wallet-topup-form">
                @csrf
                <label for="amount">Amount in Ghana cedis</label>
                <div class="wallet-amount-input"><span>GHS</span><input id="amount" type="number" name="amount" min="1" max="5000" step="0.01" value="{{ old('amount') }}" placeholder="50.00" required></div>
                <button class="button button-primary button-wide" type="submit">Top up with Paystack <span>↗</span></button>
            </form>
            <small class="wallet-secure-note">Your balance updates after Paystack confirms your payment.</small>
        </section>
        <section class="wallet-history">
            <div class="section-heading"><div><span class="eyebrow">YOUR ACTIVITY</span><h2>Wallet history</h2></div></div>
            @forelse($transactions as $transaction)
                <article class="wallet-transaction">
                    <span class="wallet-transaction-icon {{ $transaction->type === 'credit' ? 'is-credit' : 'is-debit' }}" aria-hidden="true">{{ $transaction->type === 'credit' ? '+' : '−' }}</span>
                    <div class="wallet-transaction-copy"><strong>{{ $transaction->description ?? ($transaction->type === 'credit' ? 'Wallet top up' : 'Wallet payment') }}</strong><small>{{ $transaction->created_at->format('M j, Y · g:i A') }}</small><small>Ref: {{ $transaction->reference }}</small></div>
                    <div class="wallet-transaction-amount"><strong class="{{ $transaction->type === 'credit' ? 'is-credit' : 'is-debit' }}">{{ $transaction->type === 'credit' ? '+' : '−' }} GHS {{ number_format((float) $transaction->amount, 2) }}</strong><small>{{ ucfirst($transaction->status) }}</small>
                        @if($transaction->type === 'credit' && $transaction->status === 'pending')<form method="POST" action="{{ route('wallet.topups.retry', $transaction) }}">@csrf<button class="wallet-retry-link" type="submit">Retry payment</button></form>@endif
                    </div>
                </article>
            @empty
                <div class="empty-cart wallet-empty"><h3>No wallet activity yet</h3><p>Your top ups and wallet payments will appear here.</p></div>
            @endforelse
            {{ $transactions->links() }}
        </section>
    </div>
</section>
@endsection
