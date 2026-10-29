@extends('layouts.app')

@section('title', 'Data Kartu')

@section('content')
    @php
        $isUser = auth()->check() && (auth()->user()->role ?? '') === 'user';
        $v = fn ($x) => ($x === null || $x === '') ? '-' : $x;
    @endphp

    <div class="page-head">
        <div>
            <h1>Data Kartu</h1>
            <p>Cari data kartu berdasarkan nomor kartu, NIK, nama, instansi, dan status aktif.</p>
        </div>

        <div class="page-actions">
            @if ($isUser)
                <a href="{{ route('import.peserta') }}" class="btn btn-sm">
                    @include('partials.icon', ['name' => 'upload'])
                    Import Data Kartu
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
            <form action="{{ route('kartu.index') }}" method="GET" class="filter-form">
                <div class="input-wrap">
                    <span class="lead-icon">@include('partials.icon', ['name' => 'search'])</span>
                    <input
                        class="input has-lead"
                        type="text"
                        name="keyword"
                        value="{{ $keyword }}"
                        placeholder="Cari nomor kartu, NIK, atau nama..."
                        aria-label="Kata kunci pencarian"
                    >
                </div>

                <select class="input" name="instansi" aria-label="Filter instansi">
                    <option value="">Semua instansi</option>
                    @foreach ($daftarInstansi as $namaInstansi)
                        <option value="{{ $namaInstansi }}" @selected((string) $instansi === (string) $namaInstansi)>
                            {{ $namaInstansi }}
                        </option>
                    @endforeach
                </select>

                <select class="input" name="status_aktif" aria-label="Filter status aktif">
                    <option value="">Semua status</option>
                    @foreach ($daftarStatus as $namaStatus)
                        <option value="{{ $namaStatus }}" @selected((string) $statusAktif === (string) $namaStatus)>
                            {{ $namaStatus }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn">Cari</button>
                <a href="{{ route('kartu.index') }}" class="btn btn-outline">Reset</a>
            </form>
        </section>

        {{-- Tabel data kartu --}}
        <section class="card">
            <div class="panel-head">
                <div>
                    <h2>Daftar Data Kartu</h2>
                    <p>Data pada halaman ini terpisah dari Data Peserta.</p>
                </div>

                <div class="panel-actions">
                    <span class="badge brand">Total {{ number_format($data->total(), 0, ',', '.') }} data</span>

                    <a href="{{ route('kartu.export', request()->query()) }}" class="btn btn-success btn-sm">
                        @include('partials.icon', ['name' => 'upload', 'class' => 'icon-flip'])
                        Unduh Excel
                    </a>

                    @if ($isUser && $data->total() > 0)
                        <button type="button" class="btn btn-danger-soft btn-sm" data-open-dialog="#hapusKartuDialog">
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
                        <th>TANGGAL</th>
                        <th>NO. KARTU</th>
                        <th>NIK</th>
                        <th>NAMA</th>
                        <th>INSTANSI</th>
                        <th>PENY</th>
                        <th style="background-color: #fef08a; color: #854d0e;">JAWABAN TINGGAL COPAS</th>
                        <th>PISAT</th>
                        <th>STATUS AKTIF</th>
                        <th>JNS KPST</th>
                        <th>TERDAFTAR</th>
                        <th>TUNGGAKAN</th>
                        <th>LAINNYA</th>
                        <th class="actions">AKSI</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse ($data as $item)
                        @php
                            $status = trim((string) ($item->status_aktif ?? ''));
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

                            $lain = $item->data_lain ?? [];
                            if (!is_array($lain)) {
                                $lain = json_decode((string) $lain, true) ?: [];
                            }
                        @endphp

                        <tr>
                            <td class="num">{{ $item->no_urut ?? $data->firstItem() + $loop->index }}</td>
                            <td class="mono nowrap">{{ $v($item->tanggal) }}</td>
                            <td class="mono">{{ $v($item->no_kartu) }}</td>
                            <td class="mono">{{ $v($item->nik) }}</td>
                            <td class="strong">{{ $v($item->nama) }}</td>
                            <td class="wrap">{{ $v($item->instansi) }}</td>
                            <td>{{ $v($item->peny) }}</td>
                            <td class="wrap">{{ $v($item->jawaban_tinggal_copas) }}</td>
                            <td>{{ $v($item->pisat) }}</td>
                            <td>
                                @if ($status !== '')
                                    <span class="badge {{ $kelasStatus }}">{{ $status }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $v($item->jns_kpst) }}</td>
                            <td>{{ $v($item->terdaftar) }}</td>
                            <td class="num">{{ $v($item->tunggakan) }}</td>
                            <td class="wrap">
                                @if ($lain)
                                    <div class="period-cell">
                                        @foreach ($lain as $judul => $nilai)
                                            <small>{{ $judul }}: {{ $nilai }}</small>
                                        @endforeach
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="actions">
                                <div style="display:flex;gap:8px;flex-wrap:nowrap">
                                    <a href="{{ route('kartu.show', $item->id) }}" class="btn btn-sm">Detail</a>
                                    @if ($isUser)
                                        <a href="{{ route('kartu.pemeriksaan', $item->id) }}" class="btn btn-sm">Periksa</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="empty">
                                <strong>Data kartu tidak ditemukan</strong>
                                @if ($keyword !== '' || $instansi !== '' || $statusAktif !== '')
                                    Coba periksa kembali kata kunci atau filter Anda.
                                @else
                                    Belum ada data kartu. Silakan impor file Excel Format 2 terlebih dahulu.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{ $data->links() }}
        </section>
    </div>

    {{-- Konfirmasi hapus semua data kartu --}}
    @if ($isUser && $data->total() > 0)
        <dialog id="hapusKartuDialog" class="dialog" aria-labelledby="hapusKartuTitle">
            <form action="{{ route('kartu.hapus-semua') }}" method="POST" class="dialog-body" autocomplete="off">
                @csrf
                @method('DELETE')

                <span class="dialog-icon">@include('partials.icon', ['name' => 'alert'])</span>
                <h2 id="hapusKartuTitle">Hapus semua data kartu?</h2>
                <p>
                    Seluruh data kartu akan dihapus permanen dan <strong>tidak bisa dikembalikan</strong>.
                    Data Peserta tidak ikut terhapus.
                </p>

                <div class="field">
                    <label for="konfirmasiKartu">Ketik <b>HAPUS</b> untuk melanjutkan</label>
                    <input class="input" type="text" id="konfirmasiKartu" name="konfirmasi"
                           placeholder="HAPUS" autocapitalize="characters" spellcheck="false" required>
                </div>

                <div class="dialog-actions">
                    <button type="button" class="btn btn-outline" data-close-dialog>Batal</button>
                    <button type="submit" class="btn btn-danger-soft" id="hapusKartuSubmit" disabled>Hapus Semua</button>
                </div>
            </form>
        </dialog>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        var dialog = document.getElementById('hapusKartuDialog');
        if (!dialog) { return; }

        var input = document.getElementById('konfirmasiKartu');
        var submit = document.getElementById('hapusKartuSubmit');

        function check() {
            submit.disabled = input.value.trim().toUpperCase() !== 'HAPUS';
        }

        document.querySelector('[data-open-dialog="#hapusKartuDialog"]').addEventListener('click', function () {
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