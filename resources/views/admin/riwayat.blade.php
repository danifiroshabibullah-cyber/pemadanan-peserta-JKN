@extends('layouts.app')

@section('title', 'Riwayat Pemeriksaan')

@section('content')
    <style>
        .jenis-tabs { display: flex; flex-wrap: wrap; gap: 10px; }
        .jenis-tab {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 9px 16px; border-radius: 999px;
            border: 2px solid var(--line); background: var(--surface);
            color: var(--ink); font-size: 14px; font-weight: 800; text-decoration: none;
            transition: transform .2s var(--ease), border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
        }
        .jenis-tab span {
            padding: 1px 9px; border-radius: 999px; background: var(--tint);
            color: var(--muted); font-size: 12px; font-weight: 800; font-variant-numeric: tabular-nums;
        }
        .jenis-tab:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
        .jenis-tab.semua:hover, .jenis-tab.semua.aktif { border-color: var(--brand); }
        .jenis-tab.peserta:hover, .jenis-tab.peserta.aktif { border-color: #16a34a; }
        .jenis-tab.kartu:hover, .jenis-tab.kartu.aktif { border-color: #2563eb; }
        .jenis-tab.semua.aktif { background: var(--tint); }
        .jenis-tab.peserta.aktif { background: rgba(22, 163, 74, .10); }
        .jenis-tab.kartu.aktif { background: rgba(37, 99, 235, .10); }

        .jenis-badge {
            display: inline-block; padding: 4px 12px; border-radius: 999px;
            font-size: 12px; font-weight: 800;
        }
        .jenis-badge.peserta { background: rgba(22, 163, 74, .14); color: #15803d; }
        .jenis-badge.kartu { background: rgba(37, 99, 235, .14); color: #1d4ed8; }
        html[data-theme="dark"] .jenis-badge.peserta { color: #4ade80; }
        html[data-theme="dark"] .jenis-badge.kartu { color: #93b4ff; }
    </style>

    <div class="page-head">
        <div>
            <h1>Riwayat Pemeriksaan</h1>
            <p>Pemantauan hasil pemeriksaan data peserta dan data kartu.</p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">
            @include('partials.icon', ['name' => 'arrow-left', 'class' => ''])
            Kembali ke Dashboard
        </a>
    </div>

    <div class="stack">
        <section class="card pad">
            <form action="{{ route('admin.riwayat') }}" method="GET" class="search-form">
                @if ($jenis !== 'semua')
                    <input type="hidden" name="jenis" value="{{ $jenis }}">
                @endif

                <div class="input-wrap">
                    <span class="lead-icon">@include('partials.icon', ['name' => 'search'])</span>
                    <input
                        class="input has-lead"
                        type="text"
                        name="keyword"
                        value="{{ $keyword }}"
                        placeholder="Cari nama, NIK, SK Kemensos, No. Kartu, status, atau petugas..."
                        aria-label="Kata kunci pencarian"
                    >
                </div>

                <button type="submit" class="btn">Cari</button>
                <a href="{{ route('admin.riwayat') }}" class="btn btn-outline">Reset</a>
            </form>

            <nav class="jenis-tabs" aria-label="Pilih jenis data" style="margin-top:16px">
                @foreach (['semua' => 'Semua', 'peserta' => 'Data Peserta', 'kartu' => 'Data Kartu'] as $kode => $nama)
                    <a
                        class="jenis-tab {{ $kode }} @if ($jenis === $kode) aktif @endif"
                        href="{{ route('admin.riwayat', array_filter(['keyword' => $keyword, 'jenis' => $kode === 'semua' ? null : $kode])) }}"
                        @if ($jenis === $kode) aria-current="page" @endif
                    >
                        {{ $nama }}
                        <span>{{ number_format($jumlah[$kode], 0, ',', '.') }}</span>
                    </a>
                @endforeach
            </nav>
        </section>

        <section class="card">
            <div class="table-wrap" style="border-top:0">
                <table>
                    <thead>
                    <tr>
                        <th>No.</th>
                        <th>Jenis</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>SK Kemensos / No. Kartu</th>
                        <th>Status Pemeriksaan</th>
                        <th>Catatan</th>
                        <th>Diperiksa Oleh</th>
                        <th>Waktu Pemeriksaan</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse ($riwayat as $item)
                        @php
                            $adaData = $item->nama !== null;
                            $tautan = $adaData
                                ? ($item->jenis === 'kartu'
                                    ? route('kartu.show', $item->target_id)
                                    : route('peserta.show', $item->target_id))
                                : null;
                            $nikTampil = \Illuminate\Support\Str::startsWith((string) $item->nik, \App\Models\Peserta::PREFIX_TANPA_NIK)
                                ? '(belum ada NIK)'
                                : ($item->nik ?: '-');
                        @endphp
                        <tr>
                            <td class="num">{{ $riwayat->firstItem() + $loop->index }}</td>
                            <td>
                                <span class="jenis-badge {{ $item->jenis }}">
                                    {{ $item->jenis === 'kartu' ? 'Kartu' : 'Peserta' }}
                                </span>
                            </td>
                            <td class="strong">
                                {{ $adaData ? $item->nama : ($item->jenis === 'kartu' ? 'Data kartu tidak ditemukan' : 'Data peserta tidak ditemukan') }}
                            </td>
                            <td class="mono">
                                @if ($tautan)
                                    <a href="{{ $tautan }}">{{ $nikTampil }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="mono">{{ $item->nomor ?: '-' }}</td>
                            <td>
                                @if ($item->status === 'Sesuai')
                                    <span class="badge ok">Sesuai</span>
                                @else
                                    <span class="badge bad">{{ $item->status }}</span>
                                @endif
                            </td>
                            <td class="wrap">{{ $item->catatan ?: '-' }}</td>
                            <td>{{ $item->diperiksa_oleh ?: '-' }}</td>
                            <td class="num">
                                {{ $item->diperiksa_pada
                                    ? \Illuminate\Support\Carbon::parse($item->diperiksa_pada)->format('d-m-Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty">
                                <strong>Belum ada riwayat</strong>
                                @if ($keyword !== '')
                                    Tidak ada riwayat yang cocok dengan kata kunci tersebut.
                                @else
                                    Belum ada riwayat pemeriksaan yang tersimpan.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{ $riwayat->links() }}
        </section>
    </div>
@endsection