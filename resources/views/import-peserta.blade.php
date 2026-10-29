@extends('layouts.app')

@section('title', 'Import Data Excel')

@section('content')
    <div class="page-head">
        <div>
            <h1>Import Data Excel</h1>
            <p>Impor, lihat, dan unduh data peserta maupun data kartu.</p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">
            @include('partials.icon', ['name' => 'arrow-left'])
            Dashboard
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-bad" role="alert">
            <strong>Terjadi kesalahan:</strong>
            <ul class="alert-list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="import-grid">

        {{-- ================= FORMAT 1: DATA PESERTA ================= --}}
        <section class="card pad import-card">
            <span class="pill">FORMAT 1</span>
            <h2>Data Peserta</h2>
            <p class="import-desc">
                Impor data peserta beserta status bulanan dan hasil pemeriksaan.
            </p>

            <form action="{{ route('import.peserta.proses') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <label class="dropzone">
                    <span class="stat-icon">@include('partials.icon', ['name' => 'upload'])</span>
                    <strong>Pilih File Excel Data Peserta</strong>
                    <small>Format yang didukung: .xlsx, .csv, .ods</small>
                    <input type="file" name="file" accept=".xlsx,.csv,.ods" required data-file-input="#fileName">
                    <span class="file-name" id="fileName"></span>
                </label>

                <div class="import-actions">
                    <button type="submit" class="btn btn-main">
                        @include('partials.icon', ['name' => 'upload'])
                        Import Data Peserta
                    </button>

                    <a href="{{ route('peserta.index') }}" class="btn btn-soft">Lihat Data Peserta</a>
                    <a href="{{ route('peserta.export') }}" class="btn btn-soft">Unduh Data Excel</a>
                    <a href="{{ route('import.peserta.format') }}" class="btn btn-outline" style="grid-column: 1 / -1">
                        Unduh Format Excel
                    </a>
                </div>
            </form>

            <div class="import-info">
                <h3>Format kolom Excel</h3>
                <p>
                    Kolom <b>NIK</b> dan <b>NAMA</b> wajib ada. Judul kolom boleh berada di
                    20 baris pertama.
                </p>

                <div class="chips">
                    @foreach (['NO', 'SK KEMENSOS', 'NIK', 'NAMA', 'ALAMAT', 'KEC_NAME', 'KEL_NAME', 'NO_RW', 'NO_RT', 'KETERANGAN', 'NAMA FASKES', 'Sep-26', 'SEKMEN', 'Oct-26', 'SEKMEN', 'hasil'] as $kolom)
                        <span class="chip">{{ $kolom }}</span>
                    @endforeach
                </div>

                <ul class="import-notes">
                    <li>Kolom bulan dikenali otomatis (Sep-26, Oct-26, Nov-26, dst.).</li>
                    <li>Kolom <b>SEKMEN</b> harus tepat di sebelah kanan kolom bulannya.</li>
                    <li>NIK yang sudah ada diperbarui, NIK baru ditambahkan.</li>
                    <li>Peserta tanpa NIK (misalnya bayi) tetap diimpor dan dicari lewat nama.</li>
                    <li>Format kolom NIK di Excel sebaiknya <b>Text</b>.</li>
                </ul>
            </div>
        </section>

        {{-- ================= FORMAT 2: DATA KARTU ================= --}}
        <section class="card pad import-card">
            <span class="pill ok">FORMAT 2</span>
            <h2>Data Kartu</h2>
            <p class="import-desc">
                Impor data kartu peserta dengan format kolom tersendiri.
            </p>

            <form action="{{ route('import.kartu.proses') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <label class="dropzone">
                    <span class="stat-icon">@include('partials.icon', ['name' => 'upload'])</span>
                    <strong>Pilih File Excel Data Kartu</strong>
                    <small>Format yang didukung: .xlsx, .csv, .ods</small>
                    <input type="file" name="file" accept=".xlsx,.csv,.ods" required data-file-input="#fileKartuName">
                    <span class="file-name" id="fileKartuName"></span>
                </label>

                <div class="import-actions">
                    <button type="submit" class="btn btn-main">
                        @include('partials.icon', ['name' => 'upload'])
                        Import Data Kartu
                    </button>

                    <a href="{{ route('kartu.index') }}" class="btn btn-soft">Lihat Data Kartu</a>
                    <a href="{{ route('kartu.export') }}" class="btn btn-soft">Unduh Data Excel</a>
                    <a href="{{ route('import.kartu.format') }}" class="btn btn-outline" style="grid-column: 1 / -1">
                        Unduh Format Excel
                    </a>
                </div>
            </form>

            <div class="import-info">
                <h3>Format kolom Excel</h3>
                <p>
                    Kolom <b>NIK</b> (16 digit) dan <b>NAMA</b> wajib ada. Judul kolom boleh berada di
                    20 baris pertama.
                </p>

                <div class="chips">
                    @foreach ([
                        'NO',
                        'TANGGAL',
                        'NO. KARTU',
                        'NIK',
                        'NAMA',
                        'INSTANSI',
                        'PENY',
                        'JAWABAN TINGGAL COPAS',
                        'PISAT',
                        'STATUS AKTIF',
                        'JNS KPST',
                        'TERDAFTAR',
                        'TUNGGAKAN',
                        'LAINNYA',
                    ] as $kolom)
                        <span class="chip">{{ $kolom }}</span>
                    @endforeach
                </div>

                <ul class="import-notes">
                    <li>Kolom di luar daftar ini tetap disimpan dan tampil di kolom "Lainnya".</li>
                    <li>NIK yang sudah ada diperbarui, NIK baru ditambahkan.</li>
                    <li>Baris dengan NIK bukan 16 digit atau tanpa nama dilewati dan dilaporkan.</li>
                    <li>Data Kartu disimpan terpisah dari Data Peserta.</li>
                    <li>Format kolom NIK di Excel sebaiknya <b>Text</b>.</li>
                </ul>
            </div>
        </section>

    </div>
@endsection