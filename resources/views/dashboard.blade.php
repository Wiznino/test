<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - ATU Cafeteria</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        nav {
            background: #7b001c;
            color: white;
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav h1 {
            margin: 0;
            font-size: 24px;
        }

        .logout-button {
            background: white;
            color: #7b001c;
            border: none;
            padding: 9px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .card h2 {
            color: #7b001c;
            margin-bottom: 15px;
        }

        .card p {
            color: #555;
            font-size: 16px;
            margin: 10px 0;
        }

        .menu-button {
            display: inline-block;
            margin-top: 25px;
            padding: 14px 30px;
            background: #7b001c;
            color: white;
            text-decoration: none;
            border-radius: 7px;
            font-weight: bold;
            font-size: 16px;
        }

        .menu-button:hover {
            background: #5d0015;
        }
    </style>
</head>

<body>

<nav>

    <h1>ATU Cafeteria</h1>

    <form action="/logout" method="POST">
        @csrf

        <button type="submit" class="logout-button">
            Logout
        </button>
    </form>

</nav>

<div class="container">

    <div class="card">

        <h2>
            Welcome, {{ Auth::user()->name }}! 👋
        </h2>

        <p>
            You are successfully logged into your ATU Cafeteria account.
        </p>

        <p>
            Your email: {{ Auth::user()->email }}
        </p>

        <p>
            Your phone: {{ Auth::user()->phone }}
        </p>

        <a href="/menu" class="menu-button">
            🍔 View Menu
        </a>

    </div>

</div>

</body>
</html>
