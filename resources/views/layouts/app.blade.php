<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    @include('partials.head')
    <title>@yield('title', 'Dashboard') | Pemadanan Peserta</title>
    @stack('styles')
</head>
<body>
    @php
        $me = auth()->user();
        $isAdmin = $me && $me->role === 'admin';
    @endphp

    <header class="topbar">
        <div class="topbar-in">
            <a href="{{ route('dashboard') }}" class="brand">
                <span class="brand-mark">
                    @include('partials.icon', ['name' => 'leaf'])
                </span>
                <span>Pemadanan</span>
            </a>

            <nav class="nav" aria-label="Menu utama">
                <a href="{{ route('dashboard') }}"
                   @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                    Dashboard
                </a>

                <a href="{{ route('peserta.index') }}"
                   @if (request()->routeIs('peserta.*')) aria-current="page" @endif>
                    Data Peserta
                </a>

                @if (\Illuminate\Support\Facades\Route::has('kartu.index'))
                    <a href="{{ route('kartu.index') }}"
                       @if (request()->routeIs('kartu.*')) aria-current="page" @endif>
                        Data Kartu
                    </a>
                @endif

                @if ($isAdmin)
                    <a href="{{ route('admin.riwayat') }}"
                       @if (request()->routeIs('admin.riwayat')) aria-current="page" @endif>
                        Riwayat Pemeriksaan
                    </a>
                @else
                    <a href="{{ route('import.peserta') }}"
                       @if (request()->routeIs('import.*')) aria-current="page" @endif>
                        Import Excel
                    </a>
                @endif
            </nav>

            <div class="topbar-end">
                <a href="{{ route('profile.edit') }}"
                   class="me-chip"
                   title="Profil saya"
                   @if (request()->routeIs('profile.*')) aria-current="page" @endif>

                    @include('partials.avatar', [
                        'user' => $me,
                        'size' => 'sm'
                    ])

                    <span class="me-text">
                        <b>{{ \Illuminate\Support\Str::limit($me->name, 18) }}</b>
                        <small>{{ $isAdmin ? 'Admin' : 'Petugas' }}</small>
                    </span>
                </a>

                @include('partials.theme-toggle')

                <form action="{{ route('logout') }}"
                      method="POST"
                      class="logout-form">
                    @csrf

                    <button type="submit"
                            class="btn btn-danger-soft btn-sm">
                        @include('partials.icon', ['name' => 'logout'])
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="page">
        @if (session('success'))
            <div class="alert alert-ok" role="status">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-bad" role="alert">{{ session('error') }}</div>
        @endif

        @if (session('import_errors'))
            <div class="alert alert-bad" role="alert">
                <strong>Baris yang dilewati / gagal:</strong>
                <ul class="import-errors">
                    @foreach (session('import_errors') as $catatan)
                        @if (is_array($catatan))
                            <li>
                                Baris {{ $catatan['baris'] ?? '?' }}
                                ({{ $catatan['nama'] ?? '-' }} / {{ $catatan['nik'] ?? '-' }}):
                                {{ $catatan['error'] ?? '' }}
                            </li>
                        @else
                            <li>{{ $catatan }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="{{ asset('js/theme.js') }}" defer></script>
    @stack('scripts')
</body>
</html>