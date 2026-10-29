@extends('layouts.app')

@section('title', 'Pemeriksaan Data Kartu')

@section('content')
    @php
        $v = fn ($x) => ($x === null || $x === '') ? '-' : $x;
    @endphp

    <a class="back-link" href="{{ route('kartu.show', $kartu->id) }}">
        @include('partials.icon', ['name' => 'arrow-left', 'class' => ''])
        Kembali ke detail kartu
    </a>

    <div class="page-head">
        <div>
            <h1>Formulir Pemeriksaan Data Kartu</h1>
            <p>
                Periksa data kartu berdasarkan sumber pembanding yang berwenang.
                Status pada halaman ini hanya mencatat hasil pemeriksaan internal,
                bukan mengubah status kepesertaan.
            </p>
        </div>
    </div>

    <div class="stack" style="max-width:760px">
        <section class="card pad">
            <div class="info-grid">
                <div class="info-item"><span>Nama</span><strong>{{ $v($kartu->nama) }}</strong></div>
                <div class="info-item"><span>Tanggal</span><strong>{{ $v($kartu->tanggal) }}</strong></div>
                <div class="info-item"><span>NIK</span><strong>{{ $v($kartu->nik) }}</strong></div>
                <div class="info-item"><span>No. Kartu</span><strong>{{ $v($kartu->no_kartu) }}</strong></div>
                <div class="info-item"><span>Instansi</span><strong>{{ $v($kartu->instansi) }}</strong></div>
                <div class="info-item"><span>Status Aktif</span><strong>{{ $v($kartu->status_aktif) }}</strong></div>
                <div class="info-item"><span>Jns Kpst</span><strong>{{ $v($kartu->jns_kpst) }}</strong></div>
                <div class="info-item"><span>Terdaftar</span><strong>{{ $v($kartu->terdaftar) }}</strong></div>
                <div class="info-item"><span>Tunggakan</span><strong>{{ $v($kartu->tunggakan) }}</strong></div>
            </div>
        </section>

        <section class="card pad">
            @if ($errors->any())
                <div class="alert alert-bad" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('kartu.pemeriksaan.simpan', $kartu->id) }}" method="POST">
                @csrf

                <div class="field">
                    <label for="status">Hasil Pemeriksaan</label>
                    <select name="status" id="status" class="input" required>
                        <option value="">-- Pilih hasil pemeriksaan --</option>
                        <option value="Sesuai"
                            {{ old('status', $pemeriksaan->status ?? '') === 'Sesuai' ? 'selected' : '' }}>
                            Sesuai
                        </option>
                        <option value="Perlu ditindaklanjuti"
                            {{ old('status', $pemeriksaan->status ?? '') === 'Perlu ditindaklanjuti' ? 'selected' : '' }}>
                            Perlu ditindaklanjuti
                        </option>
                    </select>
                </div>

                <div class="field">
                    <label for="catatan">Catatan Pemeriksaan</label>
                    <textarea name="catatan" id="catatan" class="input"
                        placeholder="Contoh: Status aktif perlu dikonfirmasi kembali.">{{ old('catatan', $pemeriksaan->catatan ?? '') }}</textarea>
                </div>

                <div class="field">
                    <label for="diperiksa_oleh">Nama Petugas</label>
                    <input type="text" name="diperiksa_oleh" id="diperiksa_oleh" class="input"
                        value="{{ old('diperiksa_oleh', $pemeriksaan->diperiksa_oleh ?? '') }}"
                        maxlength="255"
                        placeholder="Masukkan nama petugas">
                </div>

                <div class="hero-actions" style="margin-top:8px">
                    <button type="submit" class="btn">Simpan Pemeriksaan</button>
                    <a href="{{ route('kartu.show', $kartu->id) }}" class="btn btn-outline">Kembali</a>
                </div>
            </form>
        </section>
    </div>
@endsection