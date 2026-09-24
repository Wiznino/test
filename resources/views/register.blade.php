<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | ATU Cafeteria</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .register-box {
            width: 100%;
            max-width: 450px;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            color: #7b001c;
            font-size: 28px;
        }

        .header p {
            color: #666;
            margin-top: 8px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        .error-box {
            background: #ffe5e5;
            border: 1px solid #ffb3b3;
            color: #b00020;
            padding: 12px 15px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        .error-box ul {
            margin: 8px 0 0 20px;
            padding: 0;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 7px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #7b001c;
        }

        .register-button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 7px;
            background: #7b001c;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .register-button:hover {
            background: #5d0015;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .login-link a {
            color: #7b001c;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="register-box">

    <div class="header">
        <h1>ATU CAFETERIA</h1>
        <p>Food Ordering & Pickup System</p>
    </div>

    <h2>Create Account</h2>

    {{-- Display validation errors --}}
    @if ($errors->any())
        <div class="error-box">
            <strong>Please correct the following:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ url('/register') }}" method="POST">

        @csrf

        <div class="form-group">
            <label for="name">Full Name</label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="Enter your full name"
                required
            >
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>

            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Enter your email address"
                required
            >
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>

            <input
                type="text"
                id="phone"
                name="phone"
                value="{{ old('phone') }}"
                placeholder="Enter your phone number"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
            >
        </div>

        <div class="form-group">
            <label for="password_confirmation">
                Confirm Password
            </label>

            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="Confirm your password"
                required
            >
        </div>

        <button type="submit" class="register-button">
            Create Account
        </button>

    </form>

    <div class="login-link">
        Already have an account?
        <a href="{{ url('/login') }}">Login here</a>
    </div>

</div>

</body>
</html>
