@extends('layouts.app')

@section('title', 'Data Peserta')

@section('content')
    @php
        $isUser = auth()->check() && (auth()->user()->role ?? '') === 'user';
        $v = fn ($x) => ($x === null || $x === '') ? '-' : $x;
    @endphp

    <div class="page-head">
        <div>
            <h1>Data Peserta</h1>
            <p>Cari peserta, lihat status bulanan, dan catat hasil pemeriksaan.</p>
        </div>

        <div class="page-actions">
            @if ($isUser)
                <a href="{{ route('import.peserta') }}" class="btn btn-sm">
                    @include('partials.icon', ['name' => 'upload'])
                    Import Excel
                </a>
            @endif

            <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">
                @include('partials.icon', ['name' => 'arrow-left'])
                Dashboard
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-bad" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="stack">
        {{-- Pencarian dan filter --}}
        <section class="card pad">
            <form action="{{ route('peserta.index') }}" method="GET" class="filter-form">
                <div class="input-wrap">
                    <span class="lead-icon">@include('partials.icon', ['name' => 'search'])</span>
                    <input
                        class="input has-lead"
                        type="text"
                        name="keyword"
                        value="{{ $keyword }}"
                        placeholder="Cari NIK, nama, SK Kemensos, alamat, wilayah, atau faskes..."
                        aria-label="Kata kunci pencarian"
                    >
                </div>

                <select class="input" name="kecamatan" aria-label="Filter kecamatan">
                    <option value="">Semua kecamatan</option>
                    @foreach ($daftarKecamatan as $nama)
                        <option value="{{ $nama }}" @selected($kecamatan === $nama)>{{ $nama }}</option>
                    @endforeach
                </select>

                <select class="input" name="nik_status" aria-label="Filter NIK">
                    <option value="">Semua status NIK</option>
                    <option value="ada" @selected($filterNik === 'ada')>Punya NIK</option>
                    <option value="tanpa" @selected($filterNik === 'tanpa')>Belum ada NIK</option>
                </select>

                <button type="submit" class="btn">Cari</button>
                <a href="{{ route('peserta.index') }}" class="btn btn-outline">Reset</a>
            </form>

            <p class="hint">
                Peserta yang punya NIK dicari lewat NIK. Peserta yang belum punya NIK (misalnya bayi)
                dicari lewat <b>nama</b>, atau pilih filter "Belum ada NIK".
            </p>
        </section>

        {{-- Tabel peserta --}}
        <section class="card">
            <div class="panel-head">
                <div>
                    <h2>Daftar Data Peserta</h2>
                    <p>Geser tabel ke samping untuk melihat kolom lainnya.</p>
                </div>

                <div class="panel-actions">
                    <span class="badge brand">Total {{ number_format($peserta->total(), 0, ',', '.') }} data</span>

                    <a href="{{ route('peserta.export') }}" class="btn btn-success btn-sm">
                        @include('partials.icon', ['name' => 'upload', 'class' => 'icon-flip'])
                        Unduh Excel
                    </a>

                    @if ($isUser && $totalSemua > 0)
                        <button type="button" class="btn btn-danger-soft btn-sm" data-open-dialog="#hapusDialog">
                            @include('partials.icon', ['name' => 'alert'])
                            Hapus Semua
                        </button>
                    @endif
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th class="num">NO</th>
                        <th>SK KEMENSOS</th>
                        <th>NIK</th>
                        <th>NAMA</th>
                        <th>ALAMAT</th>
                        <th>KEC_NAME</th>
                        <th>KEL_NAME</th>
                        <th>NO_RW</th>
                        <th>NO_RT</th>
                        <th>KETERANGAN</th>
                        <th>NAMA FASKES</th>
                        @foreach ($periodeAktif as $periode)
                            <th>{{ \App\Models\PesertaPeriode::labelPeriode($periode) }}</th>
                            <th>SEKMEN</th>
                        @endforeach
                        <th>hasil</th>
                        <th>Pemeriksaan</th>
                        <th class="actions">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse ($peserta as $item)
                        @php
                            $periodeItem = ($dataPeriode[$item->id] ?? collect())->keyBy('periode');
                            $h = strtoupper((string) $item->hasil);
                            $pem = optional($item->pemeriksaanTerakhir)->status;
                        @endphp

                        <tr>
                            <td class="num">{{ $item->no_urut ?? $peserta->firstItem() + $loop->index }}</td>
                            <td>{{ $v($item->sk_kemensos) }}</td>
                            <td class="mono">
                                @if ($item->tanpa_nik)
                                    <span class="badge muted">{{ $item->nik_tampil }}</span>
                                @else
                                    {{ $item->nik }}
                                @endif
                            </td>
                            <td class="strong">{{ $item->nama }}</td>
                            <td class="wrap">{{ $v($item->alamat) }}</td>
                            <td>{{ $v($item->kecamatan) }}</td>
                            <td>{{ $v($item->kelurahan) }}</td>
                            <td class="num">{{ $v($item->rw) }}</td>
                            <td class="num">{{ $v($item->rt) }}</td>
                            <td class="wrap">{{ $v($item->keterangan) }}</td>
                            <td>{{ $v($item->faskes) }}</td>

                            @foreach ($periodeAktif as $periode)
                                @php $p = $periodeItem->get($periode); @endphp
                                <td>{{ $v(optional($p)->status) }}</td>
                                <td>{{ $v(optional($p)->segmen) }}</td>
                            @endforeach

                            <td>
                                @if ($h === 'TRUE')
                                    <span class="badge ok">TRUE</span>
                                @elseif ($h === 'FALSE')
                                    <span class="badge bad">FALSE</span>
                                @else
                                    {{ $v($item->hasil) }}
                                @endif
                            </td>

                            <td>
                                @if ($pem === 'Sesuai')
                                    <span class="badge ok">Sesuai</span>
                                @elseif ($pem)
                                    <span class="badge bad">{{ $pem }}</span>
                                @else
                                    <span class="badge muted">Belum diperiksa</span>
                                @endif
                            </td>

                            <td class="actions">
                                <div class="action-group">
                                    <a href="{{ route('peserta.show', $item->id) }}" class="btn btn-soft btn-sm">Detail</a>

                                    @if ($isUser)
                                        <a href="{{ route('peserta.pemeriksaan', $item->id) }}" class="btn btn-sm">Periksa</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 14 + $periodeAktif->count() * 2 }}" class="empty">
                                <strong>Data tidak ditemukan</strong>
                                @if ($keyword !== '' || $kecamatan !== '' || $filterNik !== '')
                                    Coba periksa kembali kata kunci atau filter Anda.
                                @else
                                    Belum ada data peserta. Silakan impor file Excel terlebih dahulu.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{ $peserta->links() }}
        </section>
    </div>

    {{-- Konfirmasi hapus semua data --}}
    @if ($isUser && $totalSemua > 0)
        <dialog id="hapusDialog" class="dialog" aria-labelledby="hapusTitle">
            <form action="{{ route('peserta.hapus-semua') }}" method="POST" class="dialog-body" autocomplete="off">
                @csrf
                @method('DELETE')

                <span class="dialog-icon">@include('partials.icon', ['name' => 'alert'])</span>
                <h2 id="hapusTitle">Hapus semua data peserta?</h2>
                <p>
                    Sebanyak <strong>{{ number_format($totalSemua, 0, ',', '.') }} data peserta</strong>
                    beserta data bulanan dan seluruh riwayat pemeriksaannya akan dihapus permanen dan
                    <strong>tidak bisa dikembalikan</strong>. Data Kartu tidak ikut terhapus.
                </p>

                <div class="field">
                    <label for="konfirmasi">Ketik <b>HAPUS</b> untuk melanjutkan</label>
                    <input class="input" type="text" id="konfirmasi" name="konfirmasi"
                           placeholder="HAPUS" autocapitalize="characters" spellcheck="false" required>
                </div>

                <div class="dialog-actions">
                    <button type="button" class="btn btn-outline" data-close-dialog>Batal</button>
                    <button type="submit" class="btn btn-danger-soft" id="hapusSubmit" disabled>Hapus Semua</button>
                </div>
            </form>
        </dialog>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        var dialog = document.getElementById('hapusDialog');
        if (!dialog) { return; }

        var input = document.getElementById('konfirmasi');
        var submit = document.getElementById('hapusSubmit');

        function check() {
            submit.disabled = input.value.trim().toUpperCase() !== 'HAPUS';
        }

        document.querySelector('[data-open-dialog="#hapusDialog"]').addEventListener('click', function () {
            input.value = '';
            check();
            dialog.showModal();
            input.focus();
        });

        dialog.querySelector('[data-close-dialog]').addEventListener('click', function () { dialog.close(); });

        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) { dialog.close(); }
        });

        input.addEventListener('input', check);
    })();
</script>
@endpush