<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cart - ATU Cafeteria</title>

    <link rel="stylesheet" href="{{ asset('css/atu.css') }}">
</head>

<body>

    <div style="max-width: 900px; margin: 0 auto; padding: 30px 20px;">

        <h1 style="text-align: center;">
            🛒 Your Cart
        </h1>

        @if (count($cart) > 0)

            @php
                $total = 0;
            @endphp

            @foreach ($cart as $id => $item)

                @php
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                @endphp

                <div class="atu-card" style="margin-bottom: 20px; padding: 20px;">

                    <h2>
                        {{ $item['name'] }}
                    </h2>

                    <p>
                        Price: GH₵ {{ number_format($item['price'], 2) }}
                    </p>

                    <p>
                        Quantity: {{ $item['quantity'] }}
                    </p>

                    <p class="atu-price">
                        Subtotal:
                        GH₵ {{ number_format($subtotal, 2) }}
                    </p>
                    <form action="{{ url('/cart/remove/' . $id) }}" method="POST">
    @csrf

    <button
        type="submit"
        style="
            background: #dc2626;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        "
    >
        🗑️ Remove
    </button>
</form>


                </div>

            @endforeach

            <div class="atu-card" style="padding: 20px; text-align: center;">

                <h2>
                    Total:
                    GH₵ {{ number_format($total, 2) }}
                </h2>

                <a href="/menu"
                   class="atu-button"
                   style="display: inline-block; text-decoration: none; margin-top: 10px;">
                    Continue Shopping
                </a>

            </div>

        @else

            <div class="atu-card" style="padding: 30px; text-align: center;">

                <h2>Your cart is empty</h2>

                <a href="/menu"
                   class="atu-button"
                   style="display: inline-block; text-decoration: none;">
                    Browse Menu
                </a>

            </div>

        @endif

    </div>

</body>
</html>
