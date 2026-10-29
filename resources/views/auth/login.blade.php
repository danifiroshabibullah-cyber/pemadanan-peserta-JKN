@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <h1>Selamat datang</h1>
    <p class="sub">Masuk ke akun Anda untuk melanjutkan.</p>

    {{-- Pesan registrasi berhasil --}}
    @if (session('success'))
        <div class="alert alert-ok" role="status">{{ session('success') }}</div>
    @endif

    {{-- Pesan kesalahan --}}
    @if ($errors->any())
        <div class="alert alert-bad" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Pilihan jenis login: Pengguna lebih dulu --}}
    @php($startRole = old('login_role', session('login_role', 'user')))
    <div class="seg" role="tablist" aria-label="Jenis login" style="--i: {{ $startRole === 'admin' ? 1 : 0 }}">
        <span class="seg-thumb" aria-hidden="true"></span>
        <button type="button" role="tab" id="userTab" data-role="user" aria-selected="{{ $startRole === 'user' ? 'true' : 'false' }}">
            Login Pengguna
        </button>
        <button type="button" role="tab" id="adminTab" data-role="admin" aria-selected="{{ $startRole === 'admin' ? 'true' : 'false' }}">
            Login Admin
        </button>
    </div>

    <form id="loginForm" class="form-swap" action="{{ route($startRole === 'admin' ? 'login.admin' : 'login.user') }}" method="POST">
        @csrf
        <input type="hidden" name="login_role" id="loginRole" value="{{ $startRole }}">

        <div class="field">
            <label for="email">Email</label>
            <div class="input-wrap">
                <span class="lead-icon">@include('partials.icon', ['name' => 'mail'])</span>
                <input
                    class="input has-lead"
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="nama@gmail.com"
                    pattern="[a-zA-Z0-9._%+-]+@gmail\.com"
                    maxlength="254"
                    autocomplete="username"
                    required
                >
            </div>
        </div>

        @include('partials.password-field', [
            'id' => 'password',
            'label' => 'Kata Sandi',
            'placeholder' => 'Masukkan kata sandi',
            'autocomplete' => 'current-password',
        ])

        <button type="submit" class="btn btn-block" id="submitButton">
            Masuk sebagai {{ $startRole === 'admin' ? 'Admin' : 'Pengguna' }}
        </button>
    </form>

    {{-- Registrasi hanya untuk Pengguna --}}
    <p class="auth-foot register-row" id="registerSection" @if ($startRole === 'admin') hidden @endif>
        Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
    </p>

    <p class="auth-note">
        Gunakan email berakhiran @gmail.com dan kata sandi 8–12 karakter.
        Pengguna harus mendaftar terlebih dahulu sebelum login.
    </p>
@endsection

@push('scripts')
<script>
    (function () {
        var routes = {
            user:  { action: @json(route('login.user')),  label: 'Masuk sebagai Pengguna' },
            admin: { action: @json(route('login.admin')), label: 'Masuk sebagai Admin' }
        };

        var seg = document.querySelector('.seg');
        var form = document.getElementById('loginForm');
        var submit = document.getElementById('submitButton');
        var register = document.getElementById('registerSection');
        var tabs = document.querySelectorAll('.seg [role="tab"]');

        function setRole(role) {
            seg.style.setProperty('--i', role === 'admin' ? 1 : 0);
            tabs.forEach(function (t) {
                t.setAttribute('aria-selected', t.dataset.role === role ? 'true' : 'false');
            });

            form.action = routes[role].action;
            document.getElementById('loginRole').value = role;
            submit.textContent = routes[role].label;
            register.hidden = role === 'admin';

            // Putar ulang animasi pergantian form
            form.classList.remove('swap');
            void form.offsetWidth;
            form.classList.add('swap');
        }

        tabs.forEach(function (t) {
            t.addEventListener('click', function () { setRole(t.dataset.role); });
        });
    })();
</script>
@endpush
