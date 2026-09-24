<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ATU Cafeteria</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0057B8">
    <link rel="stylesheet" href="{{ asset('css/atu.css') }}">
     <link rel="apple-touch-icon" href="/icons/icon-192.png">
     <meta name="apple-mobile-web-app-capable" content="yes">
     <meta name="apple-mobile-web-app-status-bar-style" content="default">
     <meta name="apple-mobile-web-app-title" content="ATU Cafeteria">
    <script>
        if ('serviceWorker' in navigator){
            window.addEventListener('load', function (){
                navigator.serviceWorker.register('/sw.js');
            });
        }
        </script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        nav {
            background: #8B0000;
            color: white;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav h2 {
            margin: 0;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 25px;
        }

        .hero {
            background: white;
            text-align: center;
            padding: 70px 20px;
        }

        .hero h1 {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .hero p {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }

        .button {
            background: #8B0000;
            color: white;
            padding: 14px 25px;
            text-decoration: none;
            border-radius: 6px;
        }

        .menu {
            padding: 50px;
        }

        .menu h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .foods {
            display: flex;
            gap: 25px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .food {
            background: white;
            width: 250px;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .food-icon {
            font-size: 60px;
        }

        .price {
            color: #8B0000;
            font-weight: bold;
            margin: 15px;
        }
    </style>
</head>

<body>

    <nav>
        <h2>ATU Cafeteria</h2>

        <div>
            <a href="/">Home</a>
            <a href="#">Menu</a>
            <a href="#">My Orders</a>
            <a href="#">Login</a>
        </div>
    </nav>

    <section class="hero">

        <h1>Order Your Food Before You Arrive</h1>

        <p>
            Skip the long cafeteria queue and pick up your food when it is ready.
        </p>

        <a href="/menu" class="button">View Menu</a>

    </section>

    <section class="menu" id="menu">

        <h2>Popular Meals</h2>

        <div class="foods">

            <div class="food">
                <div class="food-icon">🍚</div>

                <h3>Jollof Rice</h3>

                <p>Jollof rice served with chicken.</p>

                <div class="price">GH₵ 35.00</div>

                <a href="#" class="button">Order Now</a>
            </div>


            <div class="food">
                <div class="food-icon">🍗</div>

                <h3>Fried Rice</h3>

                <p>Fried rice served with chicken.</p>

                <div class="price">GH₵ 40.00</div>

                <a href="#" class="button">Order Now</a>
            </div>


            <div class="food">
                <div class="food-icon">🍝</div>

                <h3>Spaghetti</h3>

                <p>Spaghetti with chicken and vegetables.</p>

                <div class="price">GH₵ 30.00</div>

                <a href="#" class="button">Order Now</a>
            </div>

        </div>

    </section>

</body>
</html>
