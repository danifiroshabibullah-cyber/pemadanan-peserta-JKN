@extends('layouts.app')

@section('title', 'Halaman Kerja')

@section('content')
    <section class="hero">
        <div>
            <span class="pill">Halaman Kerja Pengguna</span>
            <h1>Halo, <em>{{ auth()->user()->name }}</em></h1>
            <p class="lead">Selamat datang di Sistem Pemadanan Data Peserta. Silakan pilih pekerjaan yang ingin dilakukan.</p>

            <div class="hero-actions">
                <a href="{{ route('peserta.index') }}" class="btn">
                    Buka Data Peserta
                    <span class="dot">@include('partials.icon', ['name' => 'arrow-right'])</span>
                </a>
                <a href="{{ route('import.peserta') }}" class="btn btn-outline">Import Excel</a>
            </div>
        </div>

        <div class="hero-visual">
            <div class="float-card" style="width:min(100%,340px)">
                <small style="display:block;color:var(--muted);font-weight:700;font-size:13px">Alur kerja</small>
                <ol class="steps" style="grid-template-columns:1fr">
                    <li><span><strong>Import data</strong>Masukkan file Excel peserta.</span></li>
                    <li><span><strong>Cari peserta</strong>Berdasarkan nama, NIK, atau wilayah.</span></li>
                    <li><span><strong>Periksa dan simpan</strong>Catat hasil pemeriksaan data.</span></li>
                </ol>
            </div>
        </div>
    </section>

    <section class="services">
        <article class="card service">
            <span class="stat-icon">@include('partials.icon', ['name' => 'users'])</span>
            <h2>Data Peserta</h2>
            <p>Cari peserta berdasarkan nama, NIK, SK Kemensos, atau wilayah, lalu lihat detailnya.</p>
            <a href="{{ route('peserta.index') }}" class="btn btn-soft">
                Buka Data Peserta
                <span class="dot">@include('partials.icon', ['name' => 'arrow-right'])</span>
            </a>
        </article>

        <article class="card service">
            <span class="stat-icon">@include('partials.icon', ['name' => 'clipboard'])</span>
            <h2>Data Kartu</h2>
            <p>Cari data kartu berdasarkan nomor kartu, NIK, nama, instansi, atau status aktif.</p>
            <a href="{{ route('kartu.index') }}" class="btn btn-soft">
                Buka Data Kartu
                <span class="dot">@include('partials.icon', ['name' => 'arrow-right'])</span>
            </a>
        </article>
    </section>
@endsection