@extends('layouts.auth')

@section('title', 'Registrasi')

@section('content')
    <h1>Buat akun</h1>
    <p class="sub">Daftarkan akun petugas sebelum masuk ke sistem Pemadanan Peserta.</p>

    <form action="{{ route('register.process') }}" method="POST">
        @csrf

        <div class="field">
            <label for="name">Nama lengkap</label>
            <div class="input-wrap">
                <span class="lead-icon">@include('partials.icon', ['name' => 'user'])</span>
                <input
                    class="input has-lead"
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Masukkan nama lengkap"
                    maxlength="255"
                    required
                    autocomplete="name"
                >
            </div>
            @error('name')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="email">Email Gmail</label>
            <div class="input-wrap">
                <span class="lead-icon">@include('partials.icon', ['name' => 'mail'])</span>
                <input
                    class="input has-lead"
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="nama@gmail.com"
                    pattern="[A-Za-z0-9._%+-]+@gmail\.com"
                    title="Gunakan alamat email yang berakhiran @gmail.com"
                    required
                    autocomplete="email"
                >
            </div>
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        @include('partials.password-field', [
            'id' => 'password',
            'label' => 'Kata sandi',
            'placeholder' => '8–12 karakter',
            'autocomplete' => 'new-password',
        ])

        @include('partials.password-field', [
            'id' => 'password_confirmation',
            'label' => 'Konfirmasi kata sandi',
            'placeholder' => 'Ulangi kata sandi',
            'autocomplete' => 'new-password',
        ])

        <button type="submit" class="btn btn-block">Daftar Akun</button>
    </form>

    <p class="auth-foot">
        Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
    </p>
@endsection
