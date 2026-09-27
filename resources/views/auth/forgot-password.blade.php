@extends('layouts.app')
@section('title', 'Reset your password')
@section('content')
<section class="auth-layout"><div class="auth-main"><div class="auth-card"><div class="eyebrow">ACCOUNT RECOVERY</div><h2>Reset your password.</h2><p class="auth-subtitle">We will email you a secure link to choose a new password.</p><form method="POST" action="{{ route('password.email') }}">@csrf<div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div><button class="button button-primary button-wide" type="submit">Send reset link</button></form><div class="auth-foot"><a href="{{ route('login') }}">Back to sign in</a></div></div></div></section>
@endsection
