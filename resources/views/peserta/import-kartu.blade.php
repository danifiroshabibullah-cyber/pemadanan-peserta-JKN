@extends('layouts.app')

@section('title', 'Import Data Kartu')

@section('content')
    <div class="page-head">
        <div>
            <h1>Import Data Kartu</h1>
            <p>
                Masukkan data kartu peserta dari file Excel
                menggunakan format yang berbeda dari Data Peserta.
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">
            @include('partials.icon', ['name' => 'arrow-left', 'class' => ''])
            Dashboard
        </a>
    </div>

    <div class="stack" style="max-width: 1000px">

        {{-- Pesan kesalahan --}}
        @if ($errors->any())
            <div class="alert alert-bad" role="alert">
                <ul class="alert-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Pesan berhasil --}}
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        {{-- Pesan gagal --}}
        @if (session('error'))
            <div class="alert alert-bad" role="alert">
                {{ session('error') }}
            </div>
        @endif

        {{-- FORMAT 2: DATA KARTU --}}
        <section class="card pad">
            <span class="format-badge-kartu">FORMAT 2</span>

            <h2 class="section-title" style="margin-top: 16px">
                Data Kartu
            </h2>

            <p class="hint" style="margin-top: 0">
                Pilih file Excel yang berisi data kartu peserta.
                Data akan disimpan pada tabel Data Kartu secara terpisah
                dari Data Peserta.
            </p>

            <form
                action="{{ route('import.kartu.proses') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                <label class="dropzone">
                    <span class="stat-icon">
                        @include('partials.icon', ['name' => 'upload'])
                    </span>

                    <strong>Pilih File Excel Data Kartu</strong>

                    <small>
                        Format yang didukung: .xlsx, .csv, dan .ods
                    </small>

                    <input
                        type="file"
                        name="file"
                        accept=".xlsx,.csv,.ods"
                        required
                        data-file-input="#fileKartuName"
                    >

                    <span class="file-name" id="fileKartuName"></span>
                </label>

                <div class="hero-actions" style="margin-top: 20px">
                    <button type="submit" class="btn btn-kartu">
                        Import Data Kartu
                    </button>

                    <a
                        href="{{ route('import.kartu.format') }}"
                        class="btn btn-outline"
                    >
                        Unduh Format Excel
                    </a>

                    <a
                        href="{{ route('kartu.index') }}"
                        class="btn btn-outline"
                    >
                        Lihat Data Kartu
                    </a>
                </div>
            </form>
        </section>

        {{-- INFORMASI FORMAT KOLOM --}}
        <section class="card pad">
            <h2 class="section-title">Format Kolom Excel</h2>

            <p class="hint" style="margin-top: 0">
                Baris judul dapat berada di antara 20 baris pertama.
                Kolom NIK dan NAMA wajib tersedia.
            </p>

            <div class="chips">
                @foreach ([
                    'No',
                    'NO. KARTU',
                    'NIK',
                    'NAMA',
                    'INSTANSI',
                    'Penyelenggara',
                    'Jawaban Tinggal Copas',
                    'PISAT',
                    'Status Aktif',
                    'Jns Kpst',
                    'Terdaftar',
                    'Tunggakan'
                ] as $kolom)
                    <span class="chip">{{ $kolom }}</span>
                @endforeach
            </div>

            <ul
                class="alert-list"
                style="margin-top: 18px; color: var(--muted); font-size: 13.5px; line-height: 1.7"
            >
                <li>Pastikan kolom NIK dan NAMA tersedia.</li>
                <li>Sebaiknya kolom NIK menggunakan format Text di Excel.</li>
                <li>Data Kartu tidak menggantikan atau menghapus Data Peserta.</li>
            </ul>
        </section>
    </div>

    <style>
        .format-badge-kartu {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 999px;
            background: #eaf1ff;
            color: #245bc1;
            border: 1px solid #c5d7ff;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .5px;
        }

        .btn-kartu {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .btn-kartu:hover {
            background: #1d4ed8;
        }
    </style>
@endsection