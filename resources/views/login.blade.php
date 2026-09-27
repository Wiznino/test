@extends('layouts.app')

@section('title', 'Sign in')

@section('content')
<section class="auth-layout">
    <div class="auth-aside">
        <div class="eyebrow"><span class="eyebrow-dot"></span> YOUR CAMPUS LUNCH BREAK, MADE BETTER</div>
        <h1>More lunch.<br><em>Less line.</em></h1>
        <p>Sign in to pick up right where your day left off.</p>
        <div class="auth-aside-foot">✳ &nbsp; Fresh food. Your schedule.</div>
    </div>

    <div class="auth-main">
        <div class="auth-card">
            <div class="eyebrow">WELCOME BACK</div>
            <h2>Good to see you.</h2>
            <p class="auth-subtitle">Sign in to your ATU Eats account.</p>

            @if (session('success'))
                <div class="inline-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="inline-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="field">
                    <label for="identifier">Email address</label>
                    <input id="identifier" type="text" name="identifier" value="{{ old('identifier') }}" placeholder="you@example.com" autocomplete="email" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" placeholder="Your password" autocomplete="current-password" required>
                </div>
                <div class="auth-foot auth-forgot"><a href="{{ route('password.request') }}">Forgot your password?</a></div>
                <label class="remember-line"><input type="checkbox" name="remember"> Keep me signed in</label>
                <button class="button button-primary button-wide" type="submit">Sign in <span>↗</span></button>
            </form>

            <div class="auth-foot">New to ATU Eats? <a href="{{ route('register') }}">Create your account ↗</a></div>
        </div>
    </div>
</section>
@endsection
