<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#003366">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Fresh campus favourites, ready when you are. Order from ATU Eats and skip the queue.">
    <title>@yield('title', 'ATU Eats') | Accra Technical University</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/atu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/atu-colors.css') }}">
    <link rel="stylesheet" href="{{ asset('css/atu-splash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/atu-management.css') }}">
    <link rel="stylesheet" href="{{ asset('css/atu-orders.css') }}">
    <link rel="stylesheet" href="{{ asset('css/atu-drawer.css') }}">
    <script>
        try {
            if (!sessionStorage.getItem('atu-splash-seen-v4')) {
                document.documentElement.classList.add('atu-splash-active');
                sessionStorage.setItem('atu-splash-seen-v4', '1');
            }
        } catch {}
    </script>
    @stack('head')
</head>
<body>
@php($cartCount = collect(session('cart', []))->sum('quantity'))
<div class="atu-splash" aria-hidden="true">
    <img src="{{ asset('images/atu-splash.jpg') }}" alt="Accra Technical University crest">
    <strong>ATU EATS</strong>
    <span>Fresh campus food, ready when you are.</span>
</div>
<div class="topline"><span>MADE FOR YOUR CAMPUS DAY</span><span>Accra Technical University · Accra, Ghana</span></div>
<header class="site-header">
    <button class="menu-toggle" type="button" aria-label="Open navigation menu" aria-controls="mobile-drawer" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
    <a class="brand" href="{{ route('home') }}" aria-label="ATU Eats home"><img class="brand-mark brand-crest" src="{{ asset('images/atu-splash.jpg') }}" alt="Accra Technical University crest"><span class="brand-copy">ATU <b>EATS</b><small>GOOD FOOD. ZERO QUEUE.</small></span></a>
    <nav class="main-nav" aria-label="Main navigation">
        <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
        <a class="{{ request()->routeIs('menu') ? 'active' : '' }}" href="{{ route('menu') }}">Explore menu</a>
        @auth
            @if(Auth::user()->role === 'admin')<a class="{{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Admin</a>
            @elseif(Auth::user()->role === 'vendor')<a class="{{ request()->routeIs('vendor.*') ? 'active' : '' }}" href="{{ route('vendor.dashboard') }}">Vendor workspace</a>
            @else<a class="{{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">My orders</a><a class="{{ request()->routeIs('wallet.*') ? 'active' : '' }}" href="{{ route('wallet.show') }}">Wallet</a>@endif
        @endauth
    </nav>
    <div class="header-actions">
        <a class="bag-link" href="{{ route('cart') }}" aria-label="Your bag">Bag <span>{{ $cartCount }}</span></a>
        @auth
            <a class="avatar-link" href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : (Auth::user()->role === 'vendor' ? route('vendor.dashboard') : route('dashboard')) }}">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</a>
        @else
            <a class="sign-link" href="{{ route('login') }}">Sign in <span>↗</span></a>
        @endauth
    </div>
</header>
<div class="mobile-menu-overlay" aria-hidden="true">
    <button class="drawer-backdrop" type="button" aria-label="Close navigation menu" tabindex="-1"></button>
    <aside class="mobile-drawer" id="mobile-drawer" role="dialog" aria-modal="true" aria-label="ATU Eats navigation">
        <div class="drawer-heading">
            <a class="brand" href="{{ route('home') }}" aria-label="ATU Eats home"><img class="brand-mark brand-crest" src="{{ asset('images/atu-splash.jpg') }}" alt=""><span class="brand-copy">ATU <b>EATS</b><small>GOOD FOOD. ZERO QUEUE.</small></span></a>
            <button class="drawer-close" type="button" aria-label="Close navigation menu"><span></span><span></span></button>
        </div>
        <nav class="drawer-links" aria-label="Mobile navigation">
            <a class="drawer-primary" href="{{ route('menu') }}"><span>Explore menu</span><b aria-hidden="true">›</b></a>
            @auth
                @if(Auth::user()->role === 'admin')
                    <a class="drawer-primary" href="{{ route('admin.dashboard') }}"><span>Admin dashboard</span><b aria-hidden="true">›</b></a>
                    <a class="drawer-primary" href="{{ route('admin.customers.index') }}"><span>Customers</span><b aria-hidden="true">›</b></a>
                    <a class="drawer-primary" href="{{ route('admin.vendors.index') }}"><span>Vendors</span><b aria-hidden="true">›</b></a>
                    <a class="drawer-primary" href="{{ route('admin.orders.index') }}"><span>Orders</span><b aria-hidden="true">›</b></a>
                @elseif(Auth::user()->role === 'vendor')
                    <a class="drawer-primary" href="{{ route('vendor.orders.index') }}"><span>Incoming orders</span><b aria-hidden="true">›</b></a>
                    <a class="drawer-primary" href="{{ route('vendor.meals.index') }}"><span>Manage meals</span><b aria-hidden="true">›</b></a>
                @else
                    <a class="drawer-primary" href="{{ route('orders.index') }}"><span>My orders</span><b aria-hidden="true">›</b></a>
                    <a class="drawer-primary" href="{{ route('wallet.show') }}"><span>Wallet and top up</span><b aria-hidden="true">›</b></a>
                @endif
            @else
                <a class="drawer-primary" href="{{ route('login') }}"><span>Sign in to track orders</span><b aria-hidden="true">›</b></a>
            @endauth
            <a class="drawer-primary" href="{{ route('cart') }}"><span>Your bag <small>{{ $cartCount }} {{ $cartCount === 1 ? 'item' : 'items' }}</small></span><b aria-hidden="true">›</b></a>
        </nav>
        <div class="drawer-accordions">
            <details class="drawer-accordion"><summary>Pickup information<span aria-hidden="true">⌄</span></summary><p>Choose a pickup time at checkout. Your order page shows progress as the vendor prepares your meals.</p></details>
            <div class="drawer-section-label">YOUR ACCOUNT</div>
            @auth
                <div class="drawer-account"><span class="drawer-account-mark">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span><div><strong>{{ Auth::user()->name }}</strong><small>{{ Auth::user()->role === 'vendor' && str_ends_with(Auth::user()->email, '@atu-eats.local') ? (Auth::user()->phone ?: Auth::user()->email) : Auth::user()->email }}</small></div></div>
                <a class="drawer-secondary" href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : (Auth::user()->role === 'vendor' ? route('vendor.dashboard') : route('dashboard')) }}">Account dashboard <span aria-hidden="true">›</span></a>
                <form class="drawer-signout" method="POST" action="{{ route('logout') }}">@csrf<button class="drawer-secondary" type="submit">Sign out <span aria-hidden="true">↗</span></button></form>
            @else
                <a class="drawer-secondary" href="{{ route('login') }}">Sign in <span aria-hidden="true">›</span></a>
                <a class="drawer-secondary" href="{{ route('register') }}">Create an account <span aria-hidden="true">›</span></a>
            @endauth
            <details class="drawer-accordion drawer-about"><summary>About ATU Eats<span aria-hidden="true">⌄</span></summary><p>Fresh campus food, ordered ahead from Accra Technical University vendors.</p></details>
        </div>
        <div class="drawer-footer"><span>ACCRA TECHNICAL UNIVERSITY</span><b>Good food. Zero queue.</b></div>
    </aside>
</div>
<main>
    @if(session('success'))<div class="flash flash-success" role="status"><span>✓</span> {{ session('success') }}<button type="button" aria-label="Dismiss" onclick="this.parentElement.remove()">×</button></div>@endif
    @if(session('error'))<div class="flash flash-error" role="alert">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="flash flash-error validation-flash" role="alert"><strong>Please check the form:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
@if(Auth::check() && Auth::user()->role === 'admin')
<nav class="bottom-nav" aria-label="Quick navigation">
    <a class="bottom-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg><span>Overview</span></a>
    <a class="bottom-nav-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0M17 4a4 4 0 0 1 0 8m1 3a6 6 0 0 1 4 6"/></svg><span>Customers</span></a>
    <a class="bottom-nav-item {{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}" href="{{ route('admin.vendors.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 20h18M5 20V9l7-5 7 5v11M9 20v-6h6v6"/></svg><span>Vendors</span></a>
    <a class="bottom-nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10a2 2 0 0 1 2 2v16H5V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg><span>Orders</span></a>
</nav>
@elseif(Auth::check() && Auth::user()->role === 'vendor')
<nav class="bottom-nav" aria-label="Quick navigation">
    <a class="bottom-nav-item {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}" href="{{ route('vendor.dashboard') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg><span>Overview</span></a>
    <a class="bottom-nav-item {{ request()->routeIs('vendor.meals.*') ? 'active' : '' }}" href="{{ route('vendor.meals.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4zM8 9h8M8 13h8M8 17h5"/></svg><span>Meals</span></a>
    <a class="bottom-nav-item {{ request()->routeIs('vendor.orders.*') ? 'active' : '' }}" href="{{ route('vendor.orders.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10a2 2 0 0 1 2 2v16H5V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg><span>Orders</span></a>
    <a class="bottom-nav-item" href="{{ route('home') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg><span>Store</span></a>
</nav>
@else
<nav class="bottom-nav" aria-label="Quick navigation">
    <a class="bottom-nav-item {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
        <span>Home</span>
    </a>
    <a class="bottom-nav-item {{ request()->routeIs('menu') ? 'active' : '' }}" href="{{ route('menu') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        <span>Menu</span>
    </a>
    <a class="bottom-nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10a2 2 0 0 1 2 2v16H5V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
        <span>Orders</span>
    </a>
    <a class="bottom-nav-item {{ request()->routeIs('cart', 'checkout', 'checkout.place') ? 'active' : '' }}" href="{{ route('cart') }}">
        <span class="bottom-nav-icon-wrap"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 2-1.6L21 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>@if($cartCount > 0)<span class="bottom-nav-badge">{{ $cartCount }}</span>@endif</span>
        <span>Bag</span>
    </a>
    <a class="bottom-nav-item {{ request()->routeIs('dashboard', 'login', 'register') ? 'active' : '' }}" href="{{ Auth::check() ? route('dashboard') : route('login') }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
        <span>Account</span>
    </a>
</nav>
@endif
<footer class="site-footer"><a class="brand brand-light" href="{{ route('home') }}"><img class="brand-mark brand-crest" src="{{ asset('images/atu-splash.jpg') }}" alt="Accra Technical University crest"><span class="brand-copy">ATU <b>EATS</b><small>GOOD FOOD. ZERO QUEUE.</small></span></a><p>Good food for busy campus days.</p><span>© {{ date('Y') }} Accra Technical University</span></footer>
<script>if ('serviceWorker' in navigator) window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));</script>
<script>
    if (document.documentElement.classList.contains('atu-splash-active')) {
        window.setTimeout(() => {
            document.documentElement.classList.remove('atu-splash-active');
        }, 3600);
    }
</script>
<script>
(() => {
    const toggle = document.querySelector('.menu-toggle');
    const overlay = document.querySelector('.mobile-menu-overlay');
    const drawer = document.querySelector('.mobile-drawer');
    const closeButton = document.querySelector('.drawer-close');
    const backdrop = document.querySelector('.drawer-backdrop');
    const backgroundElements = [...document.body.children].filter(element => element !== overlay && element.tagName !== 'SCRIPT');
    if (!toggle || !overlay || !drawer) return;
    const closeMenu = () => {
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('drawer-open');
        backgroundElements.forEach(element => { element.inert = false; });
        toggle.focus();
    };
    toggle.addEventListener('click', () => {
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('drawer-open');
        backgroundElements.forEach(element => { element.inert = true; });
        closeButton.focus();
    });
    closeButton.addEventListener('click', closeMenu);
    backdrop.addEventListener('click', closeMenu);
    drawer.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('drawer-open');
    }));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && overlay.classList.contains('is-open')) closeMenu();
        if (event.key === 'Tab' && overlay.classList.contains('is-open')) {
            const focusable = [...drawer.querySelectorAll('a[href], button:not([disabled]), summary')];
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });
})();
</script>
@stack('scripts')
</body>
</html>
