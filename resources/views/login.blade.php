<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/atu.css') }}">
     <link rel="apple-touch-icon" href="/icons/icon-192.png">
     <meta name="apple-mobile-web-app-capable" content="yes">
     <meta name="apple-mobile-web-app-status-bar-style" content="default">
     <meta name="apple-mobile-web-app-title" content="ATU Cafeteria">
    <title>Login - ATU Cafeteria</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .header {
            background: #00158b;
            color: white;
            padding: 20px 50px;
        }

        .header h2 {
            margin: 0;
        }

        .container {
            min-height: 90vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .login-box {
            background: white;
            width: 100%;
            max-width: 430px;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .login-box h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        .login-button {
            width: 100%;
            background: #8B0000;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        .login-button:hover {
            background: #6d0000;
        }

        .register-text {
            text-align: center;
            margin-top: 22px;
            color: #666;
        }

        .register-text a {
            color: #8B0000;
            text-decoration: none;
            font-weight: bold;
        }
        body {
    background: #EAF3FF !important;
}

h1,
h2,
h3 {
    color: #0057B8 !important;
}

button,
input[type="submit"] {
    background: #0057B8 !important;
    color: #FFFFFF !important;
    border: none !important;
}

button:hover,
input[type="submit"]:hover {
    background: #003B7A !important;
}

input {
    border: 2px solid #D1D5DB !important;
}

input:focus {
    border-color: #0057B8 !important;
    outline: none;
}

a {
    color: #0057B8 !important;
}

a:hover {
    color: #003B7A !important;
}

    </style>
</head>

<body>

    <div class="header">
        <h2>ATU Cafeteria</h2>
    </div>

    <div class="container">

        <div class="login-box">

            <h1>Welcome Back</h1>

            <p class="subtitle">
                Login to your ATU Cafeteria account.
            </p>

            @if (session('success'))
                <div class="success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/login">

                @csrf

                <div class="form-group">
                    <label>Email Address</label>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter your email"
                        value="{{ old('email') }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Password</label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button type="submit" class="login-button">
                    Login
                </button>

            </form>

            <p class="register-text">
                Don't have an account?
                <a href="/register">Create Account</a>
            </p>

        </div>

    </div>

</body>
</html>
