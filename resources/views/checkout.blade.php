@extends('layouts.app')
@section('title', 'Choose your pickup')
@section('content')
@php($cart = session('cart', []))
@php($total = collect($cart)->sum(fn ($item) => (float) $item['price'] * $item['quantity']))
@php($walletCanPay = (float) Auth::user()->wallet_balance >= $total)
<section class="page-shell">
    <div class="page-kicker"><a href="{{ route('cart') }}">&larr; Back to your cart</a><span>ALMOST THERE</span></div>
    <div class="page-title-row"><div><div class="eyebrow">THE GOOD PART IS CLOSE</div><h1>Plan your <em>pickup.</em></h1></div></div>
    @if($errors->any())<div class="inline-error">{{ $errors->first() }}</div>@endif
    @if(session('error'))<div class="inline-error">{{ session('error') }}</div>@endif
    <div class="checkout-layout">
        <form class="checkout-form" id="checkout-form" action="{{ route('checkout.place') }}" method="POST">
            @csrf
            <div class="checkout-step">
                <span>01</span>
                <div>
                    <h2>Choose a pickup time</h2>
                    <p>Times account for meal preparation, vendor hours, and current order capacity.</p>
                    <div class="field">
                        <label for="pickup_time">Available pickup slots</label>
                        <select id="pickup_time" name="pickup_time" required @disabled($pickupSlots->isEmpty())>
                            <option value="">{{ $pickupSlots->isEmpty() ? 'No pickup slots available right now' : 'Choose a pickup time' }}</option>
                            @foreach($pickupSlots as $pickupSlot)
                                <option value="{{ $pickupSlot->format('Y-m-d\TH:i') }}" @selected(old('pickup_time') === $pickupSlot->format('Y-m-d\TH:i'))>{{ $pickupSlot->format('D, M j · g:i A') }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($pickupSlots->isEmpty())
                        <p class="pickup-slots-empty">All pickup times are full or the cafeteria is closed. Please check again shortly.</p>
                    @endif
                </div>
            </div>
            <div class="checkout-step">
                <span>02</span>
                <div>
                    <h2>Anything we should know?</h2>
                    <p>Optional notes for the kitchen.</p>
                    <div class="field"><label for="notes">Order notes</label><textarea id="notes" name="notes" rows="3" maxlength="500" placeholder="Allergies or special requests">{{ old('notes') }}</textarea></div>
                </div>
            </div>
            <div class="wallet-payment-choices">
                <h2>How would you like to pay?</h2>
                <label class="wallet-payment-option"><input type="radio" name="payment_method" value="wallet" @checked(old('payment_method', $walletCanPay ? 'wallet' : 'paystack') === 'wallet') @disabled(! $walletCanPay)><span><strong>ATU Eats wallet</strong><small>Available: <x-money :amount="(float) Auth::user()->wallet_balance" /></small></span></label>
                @if((float) Auth::user()->wallet_balance < $total)<a class="wallet-topup-inline" href="{{ route('wallet.show') }}">Wallet balance is low? Top up here</a>@endif
                <label class="wallet-payment-option"><input type="radio" name="payment_method" value="paystack" @checked(old('payment_method', $walletCanPay ? 'wallet' : 'paystack') === 'paystack' || (old('payment_method') === 'wallet' && ! $walletCanPay))><span><strong>Pay with Paystack</strong><small>Card or supported mobile money options</small></span></label>
            </div>
            <button class="button button-primary checkout-submit" id="checkout-submit" type="submit" aria-live="polite" @disabled($pickupSlots->isEmpty())>Place order securely <span aria-hidden="true">&rarr;</span></button>
            <p class="checkout-feedback" id="checkout-feedback" role="status" aria-live="polite"></p>
        </form>
        <aside class="summary-card">
            <div class="eyebrow">MADE FRESH FOR YOU</div><h2>Your order</h2>
            @foreach($cart as $item)
                <div class="summary-line"><span>{{ $item['quantity'] }} &times; {{ $item['name'] }}</span><strong><x-money :amount="$item['price'] * $item['quantity']" /></strong></div>
            @endforeach
            <div class="summary-line"><span>Pickup</span><strong>Free</strong></div>
            <div class="summary-total"><span>Total</span><strong><x-money :amount="$total" /></strong></div>
            <p class="summary-note">Freshly made. Collected by you.</p>
        </aside>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.getElementById('checkout-form')?.addEventListener('submit', event => {
        const button = document.getElementById('checkout-submit');
        if (button.disabled) {
            event.preventDefault();
            return;
        }

        button.disabled = true;
        const isWalletPayment = document.querySelector('input[name="payment_method"]:checked')?.value === 'wallet';
        const feedback = document.getElementById('checkout-feedback');
        button.textContent = isWalletPayment ? 'Placing your order...' : 'Connecting to Paystack...';
        feedback.textContent = isWalletPayment
            ? 'Your wallet will be charged once this order is placed.'
            : 'We are starting your secure payment and will redirect you to Paystack.';
    });
</script>
@endpush
