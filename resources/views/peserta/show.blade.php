@extends('layouts.app')

@section('title', 'Detail Peserta')

@section('content')
    @php
        $isUser = auth()->user()->role === 'user';
        $h = strtoupper((string) $peserta->hasil);
        $pem = optional($peserta->pemeriksaan->first())->status;
        $v = fn ($x) => ($x === null || $x === '') ? '-' : $x;
    @endphp

    <a class="back-link" href="{{ route('peserta.index') }}">
        @include('partials.icon', ['name' => 'arrow-left', 'class' => ''])
        Kembali ke Data Peserta
    </a>

    <div class="page-head">
        <div>
            <h1>{{ $peserta->nama }}</h1>
            <p>{{ $peserta->tanpa_nik ? 'Belum ada NIK' : 'NIK ' . $peserta->nik }}</p>
        </div>

        <div class="panel-actions">
            @if ($pem === 'Sesuai')
                <span class="badge ok">Sesuai</span>
            @elseif ($pem)
                <span class="badge bad">{{ $pem }}</span>
            @else
                <span class="badge muted">Belum diperiksa</span>
            @endif

            @if ($isUser)
                <a href="{{ route('peserta.pemeriksaan', $peserta->id) }}" class="btn btn-sm">Periksa Data</a>
            @endif
        </div>
    </div>

    <div class="detail-grid">
        <div class="stack">
            <section class="card pad">
                <h2 class="section-title">Data Peserta</h2>

                <div class="info-grid">
                    <div class="info-item"><span>No. urut</span><strong>{{ $peserta->no_urut ?? '-' }}</strong></div>
                    <div class="info-item"><span>SK Kemensos</span><strong>{{ $peserta->sk_kemensos ?: '-' }}</strong></div>
                    <div class="info-item"><span>NIK</span><strong>{{ $peserta->nik_tampil }}</strong></div>
                    <div class="info-item"><span>Nama</span><strong>{{ $peserta->nama }}</strong></div>
                    <div class="info-item"><span>Alamat</span><strong>{{ $peserta->alamat ?: '-' }}</strong></div>
                    <div class="info-item"><span>Kecamatan</span><strong>{{ $peserta->kecamatan ?: '-' }}</strong></div>
                    <div class="info-item"><span>Kelurahan</span><strong>{{ $peserta->kelurahan ?: '-' }}</strong></div>
                    <div class="info-item"><span>NO_RW / NO_RT</span><strong>{{ $v($peserta->rw) }} / {{ $v($peserta->rt) }}</strong></div>
                    <div class="info-item"><span>hasil</span><strong>{{ $v($peserta->hasil) }}</strong></div>
                    <div class="info-item"><span>Nama faskes</span><strong>{{ $peserta->faskes ?: '-' }}</strong></div>
                    <div class="info-item"><span>Keterangan</span><strong>{{ $peserta->keterangan ?: '-' }}</strong></div>
                </div>
            </section>

            <section class="card">
                <div class="panel-head">
                    <div>
                        <h2>Status per Bulan</h2>
                        <p>Isi kolom bulan dan SEKMEN dari file Excel.</p>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Status</th>
                            <th>SEKMEN</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($peserta->periodes as $periode)
                            <tr>
                                <td class="strong">{{ \App\Models\PesertaPeriode::labelPeriode($periode->periode) }}</td>
                                <td>{{ $periode->status ?: '-' }}</td>
                                <td>{{ $periode->segmen ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty">Belum ada data bulanan untuk peserta ini.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <section class="card pad">
            <h2 class="section-title">Riwayat Pemeriksaan</h2>

            <ul class="timeline">
                @forelse ($peserta->pemeriksaan as $pemeriksaan)
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
                        <span class="tl-meta" style="margin:0">Belum ada pemeriksaan untuk peserta ini.</span>
                    </li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection