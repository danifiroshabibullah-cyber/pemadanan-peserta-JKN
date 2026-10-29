<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    @include('partials.head')
    <title>@yield('title') | Pemadanan Peserta</title>
</head>
<body>
<div class="auth-theme">
    @include('partials.theme-toggle')
</div>

<div class="auth-shell">
    <aside class="auth-hero">
        <a href="{{ route('login') }}" class="brand">
            <span class="brand-mark">@include('partials.icon', ['name' => 'leaf'])</span>
            Pemadanan
        </a>

        <div class="hero-copy">
            <h2>Data peserta lebih rapi, pemeriksaan lebih cepat.</h2>
            <p>Impor data Excel, cari peserta, dan catat hasil pemeriksaan di satu tempat.</p>
        </div>

        <svg class="hero-wave" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true">
            <path fill="currentColor" d="M0 80V40C240 0 420 0 720 30s560 30 720 0v50Z"/>
        </svg>
    </aside>

    <main class="auth-panel">
        {{-- Siluet orang berjas yang mengelilingi form --}}
        <img class="auth-figures" src="{{ asset('images/login-siluet.png') }}" alt="" aria-hidden="true" decoding="async">

        <div class="auth-card">
            @yield('content')
        </div>
    </main>
</div>

<script src="{{ asset('js/theme.js') }}" defer></script>
@stack('scripts')
</body>
</html>