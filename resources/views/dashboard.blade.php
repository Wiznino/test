<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - ATU Cafeteria</title>

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

        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
        }

        .welcome {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .welcome h1 {
            color: #8B0000;
        }

        .button {
            background: #8B0000;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <nav>
        <h2>ATU Cafeteria</h2>

        <form method="POST" action="/logout">
            @csrf
            <button type="submit" class="button">
                Logout
            </button>
        </form>
    </nav>

    <div class="container">

        <div class="welcome">

            <h1>
                Welcome, {{ Auth::user()->name }}! 👋
            </h1>

            <p>
                You are successfully logged into your ATU Cafeteria account.
            </p>

            <p>
                Your email: {{ Auth::user()->email }}
            </p>

            <p>
                Your phone: {{ Auth::user()->phone }}
            </p>

        </div>

    </div>

</body>
</html>
