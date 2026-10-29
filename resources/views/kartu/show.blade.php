@extends('layouts.app')

@section('title', 'Detail Kartu')

@section('content')
    @php
        $isUser = auth()->user()->role === 'user';
        $v = fn ($x) => ($x === null || $x === '') ? '-' : $x;

        $status = trim((string) ($kartu->status_aktif ?? ''));
        $s = strtolower($status);

        if ($status === '') {
            $kelasStatus = 'muted';
        } elseif (str_contains($s, 'tidak') || str_contains($s, 'non')) {
            $kelasStatus = 'bad';
        } elseif (str_contains($s, 'aktif')) {
            $kelasStatus = 'ok';
        } else {
            $kelasStatus = 'warn';
        }

        $pem = optional($kartu->pemeriksaan->first())->status;

        $lain = $kartu->data_lain ?? [];
        if (! is_array($lain)) {
            $lain = json_decode((string) $lain, true) ?: [];
        }
    @endphp

    <a class="back-link" href="{{ route('kartu.index') }}">
        @include('partials.icon', ['name' => 'arrow-left', 'class' => ''])
        Kembali ke Data Kartu
    </a>

    @if (session('success'))
        <div class="alert alert-ok" role="status" style="margin-bottom:16px">{{ session('success') }}</div>
    @endif

    <div class="page-head">
        <div>
            <h1>{{ $v($kartu->nama) }}</h1>
            <p>
                NIK {{ $v($kartu->nik) }}
                @if ($kartu->no_kartu)
                    · No. Kartu {{ $kartu->no_kartu }}
                @endif
            </p>
        </div>

        <div class="panel-actions">
            @if ($status !== '')
                <span class="badge {{ $kelasStatus }}">{{ $status }}</span>
            @endif

            @if ($pem === 'Sesuai')
                <span class="badge ok">Sesuai</span>
            @elseif ($pem)
                <span class="badge bad">{{ $pem }}</span>
            @else
                <span class="badge muted">Belum diperiksa</span>
            @endif

            @if ($isUser)
                <a href="{{ route('kartu.pemeriksaan', $kartu->id) }}" class="btn btn-sm">Periksa Data</a>
            @endif
        </div>
    </div>

    <div class="detail-grid">
        <div class="stack">
            <section class="card pad">
                <h2 class="section-title">Data Kartu</h2>

                <div class="info-grid">
                    <div class="info-item"><span>No. urut</span><strong>{{ $v($kartu->no_urut) }}</strong></div>
                    <div class="info-item"><span>Tanggal</span><strong>{{ $v($kartu->tanggal) }}</strong></div>
                    <div class="info-item"><span>No. Kartu</span><strong>{{ $v($kartu->no_kartu) }}</strong></div>
                    <div class="info-item"><span>NIK</span><strong>{{ $v($kartu->nik) }}</strong></div>
                    <div class="info-item"><span>Nama</span><strong>{{ $v($kartu->nama) }}</strong></div>
                    <div class="info-item"><span>Instansi</span><strong>{{ $v($kartu->instansi) }}</strong></div>
                    <div class="info-item"><span>Penyelenggara</span><strong>{{ $v($kartu->peny) }}</strong></div>
                    <div class="info-item"><span>PISAT</span><strong>{{ $v($kartu->pisat) }}</strong></div>
                    <div class="info-item"><span>Status Aktif</span><strong>{{ $v($kartu->status_aktif) }}</strong></div>
                    <div class="info-item"><span>Jns Kpst</span><strong>{{ $v($kartu->jns_kpst) }}</strong></div>
                    <div class="info-item"><span>Terdaftar</span><strong>{{ $v($kartu->terdaftar) }}</strong></div>
                    <div class="info-item"><span>Tunggakan</span><strong>{{ $v($kartu->tunggakan) }}</strong></div>
                </div>
            </section>

            <section class="card pad">
                <h2 class="section-title">Jawaban Tinggal Copas</h2>
                <p style="line-height:1.7;font-weight:600;white-space:pre-line">{{ $v($kartu->jawaban_tinggal_copas) }}</p>
            </section>

            @if ($lain)
                <section class="card pad">
                    <h2 class="section-title">Data Lainnya</h2>
                    <p class="hint" style="margin-bottom:14px">Kolom tambahan dari file Excel yang tidak termasuk kolom utama.</p>

                    <div class="info-grid">
                        @foreach ($lain as $judul => $nilai)
                            <div class="info-item">
                                <span>{{ $judul }}</span>
                                <strong>{{ $v(is_array($nilai) ? json_encode($nilai) : $nilai) }}</strong>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <section class="card pad">
            <h2 class="section-title">Riwayat Pemeriksaan</h2>

            <ul class="timeline">
                @forelse ($kartu->pemeriksaan as $pemeriksaan)
                    <li>
                        <div class="tl-top">
                            @if ($pemeriksaan->status === 'Sesuai')
                                <span class="badge ok">Sesuai</span>
                            @else
                                <span class="badge bad">{{ $pemeriksaan->status }}</span>
                            @endif

                            <span class="tl-meta" style="margin:0">
                                {{ $pemeriksaan->diperiksa_pada ? $pemeriksaan->diperiksa_pada->format('d-m-Y H:i') : '-' }}
                            </span>
                        </div>

                        <div class="tl-meta">Petugas: {{ $pemeriksaan->diperiksa_oleh ?: '-' }}</div>

                        @if ($pemeriksaan->catatan)
                            <div class="tl-note">{{ $pemeriksaan->catatan }}</div>
                        @endif
                    </li>
                @empty
                    <li>
                        <span class="tl-meta" style="margin:0">Belum ada pemeriksaan untuk kartu ini.</span>
                    </li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection