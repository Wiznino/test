<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ATU Cafeteria Menu</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .header {
            background: #7b001c;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }

        .title {
            text-align: center;
            margin-bottom: 30px;
        }

        .foods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .food {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
        }

        .food h2 {
            color: #7b001c;
        }

        .food p {
            color: #555;
        }

        .price {
            font-size: 20px;
            font-weight: bold;
            color: #7b001c;
            margin: 15px 0;
        }

        .button {
            display: block;
            width: 100%;
            padding: 12px;
            background: #7b001c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .back {
            display: inline-block;
            margin-top: 30px;
            text-decoration: none;
            color: #7b001c;
            font-weight: bold;
        }
    </style>
</head>

<body>
    @php
    $cart = session()->get('cart', []);
    $cartCount = collect($cart)->sum('quantity');
@endphp

<div style="
    display: flex;
    justify-content: flex-end;
    padding: 15px 20px;
">
    <a href="/cart" style="
        background: #0057B8;
        color: white;
        text-decoration: none;
        padding: 10px 16px;
        border-radius: 25px;
        font-weight: bold;
        font-size: 16px;
    ">
        🛒 Cart ({{ $cartCount }})
    </a>
</div>


<div class="header">
    <h1>🍔 ATU CAFETERIA</h1>
    <p>Food Menu</p>
</div>

<div class="container">

    <div class="title">
        <h1>Available Meals</h1>
        <p>Choose your favourite meal.</p>
    </div>

    @if ($foods->count() > 0)

        <div class="foods">

            @foreach ($foods as $food)

                <div class="food">

                    <h2>{{ $food->name }}</h2>

                    <p>
                        {{ $food->description }}
                    </p>

                    <div class="price">
                        GH₵ {{ number_format($food->price, 2) }}
                    </div>

                    <form action="{{ url('/cart/add/' . $food->id) }}" method="POST">
    @csrf

    <button
        type="submit"
        style="
            background: #0057B8 !important;
            color: #FFFFFF !important;
            border: none !important;
            padding: 14px 24px !important;
            border-radius: 8px !important;
            font-size: 18px !important;
            font-weight: bold !important;
            cursor: pointer !important;
            width: 100% !important;
        "
    >
        Add to Cart
    </button>
</form>

                </div>

            @endforeach

        </div>

    @else

        <h2 style="text-align:center;">
            No food available at the moment.
        </h2>

    @endif

    <div style="text-align:center;">
        <a href="/dashboard" class="back">
            ← Back to Dashboard
        </a>
    </div>

</div>

</body>
</html>
