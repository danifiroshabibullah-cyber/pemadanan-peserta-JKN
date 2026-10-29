@extends('layouts.app')

@section('title', 'Pemeriksaan Data')

@section('content')
    <a class="back-link" href="{{ route('peserta.show', $peserta->id) }}">
        @include('partials.icon', ['name' => 'arrow-left', 'class' => ''])
        Kembali ke detail peserta
    </a>

    <div class="page-head">
        <div>
            <h1>Formulir Pemeriksaan Data</h1>
            <p>
                Periksa data peserta berdasarkan sumber pembanding yang berwenang.
                Status pada halaman ini hanya mencatat hasil pemeriksaan internal,
                bukan mengubah status kepesertaan.
            </p>
        </div>
    </div>

    <div class="stack" style="max-width:760px">
        <section class="card pad">
            <div class="info-grid">
                <div class="info-item"><span>Nama</span><strong>{{ $peserta->nama }}</strong></div>
                <div class="info-item"><span>NIK</span><strong>{{ $peserta->nik_tampil }}</strong></div>
                <div class="info-item"><span>SK Kemensos</span><strong>{{ $peserta->sk_kemensos ?: '-' }}</strong></div>
                <div class="info-item"><span>Alamat</span><strong>{{ $peserta->alamat ?: '-' }}</strong></div>
                <div class="info-item"><span>Kecamatan / Kelurahan</span><strong>{{ $peserta->kecamatan ?: '-' }} / {{ $peserta->kelurahan ?: '-' }}</strong></div>
                <div class="info-item"><span>Faskes</span><strong>{{ $peserta->faskes ?: '-' }}</strong></div>
            </div>
        </section>

        <section class="card pad">
            @if ($errors->any())
                <div class="alert alert-bad" role="alert">
                    <ul class="alert-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('peserta.pemeriksaan.simpan', $peserta->id) }}" method="POST">
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
                        placeholder="Contoh: Data alamat perlu dikonfirmasi kembali.">{{ old('catatan', $pemeriksaan->catatan ?? '') }}</textarea>
                </div>

                <div class="field">
                    <label for="diperiksa_oleh">Nama Petugas</label>
                    <input type="text" name="diperiksa_oleh" id="diperiksa_oleh" class="input"
                        value="{{ old('diperiksa_oleh', auth()->user()->name) }}"
                        maxlength="255"
                        placeholder="Nama petugas">
                </div>

                <div class="hero-actions" style="margin-top:8px">
                    <button type="submit" class="btn">Simpan Pemeriksaan</button>
                    <a href="{{ route('peserta.show', $peserta->id) }}" class="btn btn-outline">Kembali</a>
                </div>
            </form>
        </section>
    </div>
@endsection