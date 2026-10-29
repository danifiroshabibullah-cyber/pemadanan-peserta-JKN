@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        // Peserta
        $pct = $totalPeserta > 0
            ? (int) round($sudahDiperiksa / $totalPeserta * 100)
            : 0;

        $offset = round(314 * (1 - $pct / 100), 1);

        $sesuaiPct = $sudahDiperiksa > 0
            ? (int) round($sesuai / $sudahDiperiksa * 100)
            : 0;

        $tindakPct = $sudahDiperiksa > 0
            ? (int) round($perluDitindaklanjuti / $sudahDiperiksa * 100)
            : 0;

        // Kartu
        $pctKartu = $totalKartu > 0
            ? (int) round($sudahDiperiksaKartu / $totalKartu * 100)
            : 0;

        $offsetKartu = round(314 * (1 - $pctKartu / 100), 1);

        $sesuaiKartuPct = $sudahDiperiksaKartu > 0
            ? (int) round($kartuSesuai / $sudahDiperiksaKartu * 100)
            : 0;

        $tindakKartuPct = $sudahDiperiksaKartu > 0
            ? (int) round($kartuPerluTindak / $sudahDiperiksaKartu * 100)
            : 0;

        $aktifPct = $totalKartu > 0
            ? (int) round($kartuAktif / $totalKartu * 100)
            : 0;

        $tidakAktifPct = $totalKartu > 0
            ? (int) round($kartuTidakAktif / $totalKartu * 100)
            : 0;

        $cocokPct = $totalKartu > 0
            ? (int) round($kartuCocok / $totalKartu * 100)
            : 0;

        $belumCocokPct = $totalKartu > 0
            ? 100 - $cocokPct
            : 0;
    @endphp

    {{-- Ringkasan utama --}}
    <section class="hero">
        <div class="hero-content">
            <span class="pill">Dashboard Admin</span>

            <h1>Pantau pemeriksaan data <em>peserta</em></h1>

            <p class="lead">
                Ringkasan pemantauan dan pemeriksaan data peserta dan data kartu,
                lengkap dengan hasil terakhir tiap data.
            </p>

            <div class="hero-actions">
                <a href="{{ route('peserta.index') }}" class="btn">
                    Lihat Data Peserta
                    <span class="dot">
                        @include('partials.icon', ['name' => 'arrow-right'])
                    </span>
                </a>

                <a href="{{ route('kartu.index') }}" class="btn btn-soft">
                    Lihat Data Kartu
                    <span class="dot">
                        @include('partials.icon', ['name' => 'arrow-right'])
                    </span>
                </a>

                <a href="{{ route('admin.riwayat') }}" class="btn btn-outline">
                    Riwayat Pemeriksaan
                </a>
            </div>
        </div>

        {{-- Visual dashboard: ganti antara Peserta dan Kartu --}}
        <div class="hero-visual dashboard-summary">

            <div class="seg summary-tabs" role="tablist" aria-label="Pilih ringkasan pemeriksaan" style="--i: 0">
                <span class="seg-thumb" aria-hidden="true"></span>

                <button type="button" role="tab" id="tabPeserta"
                        data-panel="panelPeserta" aria-controls="panelPeserta"
                        aria-selected="true">
                    Peserta
                </button>

                <button type="button" role="tab" id="tabKartu"
                        data-panel="panelKartu" aria-controls="panelKartu"
                        aria-selected="false">
                    Kartu
                </button>
            </div>

            {{-- Panel Peserta --}}
            <div class="summary-panel is-active" id="panelPeserta" role="tabpanel" aria-labelledby="tabPeserta">

                <div class="float-card donut-card">
                    <div
                        class="donut"
                        role="img"
                        aria-label="{{ $pct }} persen peserta sudah diperiksa"
                    >
                        <svg viewBox="0 0 120 120" aria-hidden="true">
                            <circle class="track" cx="60" cy="60" r="50" fill="none" stroke-width="14"/>
                            <circle class="bar" cx="60" cy="60" r="50" fill="none" stroke-width="14"
                                    style="--offset: {{ $offset }}"/>
                        </svg>

                        <b>{{ $pct }}%</b>
                    </div>

                    <div class="donut-info">
                        <small>Progres pemeriksaan</small>

                        <strong>
                            {{ $sudahDiperiksa }} dari {{ $totalPeserta }}
                            peserta sudah diperiksa
                        </strong>
                    </div>
                </div>

                <div class="float-card split-card">
                    <h3>Hasil pemeriksaan terakhir</h3>

                    <div class="split-row">
                        <div class="split-label">
                            <span>Sesuai</span>
                            <span>{{ $sesuai }}</span>
                        </div>

                        <div class="meter">
                            <i class="ok" style="--w: {{ $sesuaiPct }}%"></i>
                        </div>
                    </div>

                    <div class="split-row">
                        <div class="split-label">
                            <span>Perlu ditindaklanjuti</span>
                            <span>{{ $perluDitindaklanjuti }}</span>
                        </div>

                        <div class="meter">
                            <i class="bad" style="--w: {{ $tindakPct }}%"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel Kartu --}}
            <div class="summary-panel" id="panelKartu" role="tabpanel" aria-labelledby="tabKartu">

                <div class="float-card donut-card">
                    <div
                        class="donut"
                        role="img"
                        aria-label="{{ $pctKartu }} persen data kartu sudah diperiksa"
                    >
                        <svg viewBox="0 0 120 120" aria-hidden="true">
                            <circle class="track" cx="60" cy="60" r="50" fill="none" stroke-width="14"/>
                            <circle class="bar" cx="60" cy="60" r="50" fill="none" stroke-width="14"
                                    style="--offset: {{ $offsetKartu }}"/>
                        </svg>

                        <b>{{ $pctKartu }}%</b>
                    </div>

                    <div class="donut-info">
                        <small>Progres pemeriksaan kartu</small>

                        <strong>
                            {{ $sudahDiperiksaKartu }} dari {{ $totalKartu }}
                            kartu sudah diperiksa
                        </strong>
                    </div>
                </div>

                <div class="float-card split-card">
                    <h3>Hasil pemeriksaan terakhir</h3>

                    <div class="split-row">
                        <div class="split-label">
                            <span>Sesuai</span>
                            <span>{{ $kartuSesuai }}</span>
                        </div>

                        <div class="meter">
                            <i class="ok" style="--w: {{ $sesuaiKartuPct }}%"></i>
                        </div>
                    </div>

                    <div class="split-row">
                        <div class="split-label">
                            <span>Perlu ditindaklanjuti</span>
                            <span>{{ $kartuPerluTindak }}</span>
                        </div>

                        <div class="meter">
                            <i class="bad" style="--w: {{ $tindakKartuPct }}%"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Ringkasan Data Peserta --}}
    <h2 class="section-title" style="margin-top: 28px; margin-bottom: 0;">
        Ringkasan Data Peserta
    </h2>

    <section class="stats" aria-label="Ringkasan Data Peserta">

        <article class="card stat">
            <span class="stat-icon">
                @include('partials.icon', ['name' => 'users'])
            </span>

            <span class="stat-label">Total Peserta</span>
            <span class="stat-num">{{ $totalPeserta }}</span>
        </article>

        <article class="card stat">
            <span class="stat-icon">
                @include('partials.icon', ['name' => 'check-circle'])
            </span>

            <span class="stat-label">Sudah Diperiksa</span>
            <span class="stat-num ok">{{ $sudahDiperiksa }}</span>

            <span class="stat-note">
                {{ $pct }}% dari total peserta
            </span>
        </article>

        <article class="card stat">
            <span class="stat-icon warn">
                @include('partials.icon', ['name' => 'clock'])
            </span>

            <span class="stat-label">Belum Diperiksa</span>
            <span class="stat-num warn">{{ $belumDiperiksa }}</span>

            <span class="stat-note">
                {{ $totalPeserta > 0 ? 100 - $pct : 0 }}% dari total peserta
            </span>
        </article>

        <article class="card stat">
            <span class="stat-icon">
                @include('partials.icon', ['name' => 'shield-check'])
            </span>

            <span class="stat-label">Hasil Sesuai</span>
            <span class="stat-num ok">{{ $sesuai }}</span>
        </article>

        <article class="card stat">
            <span class="stat-icon bad">
                @include('partials.icon', ['name' => 'alert'])
            </span>

            <span class="stat-label">Perlu Ditindaklanjuti</span>
            <span class="stat-num bad">{{ $perluDitindaklanjuti }}</span>
        </article>

    </section>

    {{-- Ringkasan Data Kartu --}}
    <h2 class="section-title" style="margin-top: 28px; margin-bottom: 0;">
        Ringkasan Data Kartu
    </h2>

    <section class="stats" aria-label="Ringkasan Data Kartu">

        <article class="card stat">
            <span class="stat-icon">
                @include('partials.icon', ['name' => 'clipboard'])
            </span>

            <span class="stat-label">Total Data Kartu</span>
            <span class="stat-num">{{ $totalKartu }}</span>
        </article>

        <article class="card stat">
            <span class="stat-icon">
                @include('partials.icon', ['name' => 'check-circle'])
            </span>

            <span class="stat-label">Status Aktif</span>
            <span class="stat-num ok">{{ $kartuAktif }}</span>

            <span class="stat-note">
                {{ $aktifPct }}% dari total data kartu
            </span>
        </article>

        <article class="card stat">
            <span class="stat-icon bad">
                @include('partials.icon', ['name' => 'alert'])
            </span>

            <span class="stat-label">Tidak Aktif</span>
            <span class="stat-num bad">{{ $kartuTidakAktif }}</span>

            <span class="stat-note">
                {{ $tidakAktifPct }}% dari total data kartu
            </span>
        </article>

        <article class="card stat">
            <span class="stat-icon">
                @include('partials.icon', ['name' => 'shield-check'])
            </span>

            <span class="stat-label">Cocok dengan Data Peserta</span>
            <span class="stat-num ok">{{ $kartuCocok }}</span>

            <span class="stat-note">
                {{ $cocokPct }}% dari total data kartu
            </span>
        </article>

        <article class="card stat">
            <span class="stat-icon warn">
                @include('partials.icon', ['name' => 'clock'])
            </span>

            <span class="stat-label">Belum Cocok</span>
            <span class="stat-num warn">{{ $kartuBelumCocok }}</span>

            <span class="stat-note">
                {{ $belumCocokPct }}% dari total data kartu
            </span>
        </article>

    </section>

    {{-- Ajakan bertindak: peserta dan kartu --}}
    <div class="banners">

        {{-- Banner Peserta --}}
        <section class="banner">
            <svg
                class="deco"
                viewBox="0 0 200 200"
                fill="none"
                stroke="#fff"
                stroke-width="6"
                aria-hidden="true"
            >
                <circle cx="100" cy="100" r="90"/>
                <circle cx="100" cy="100" r="62"/>
                <circle cx="100" cy="100" r="34"/>
            </svg>

            <div>
                @if ($totalPeserta === 0)
                    <h2>Data peserta masih kosong</h2>
                    <p>Data peserta muncul setelah file Excel Format 1 diimpor.</p>
                @elseif ($belumDiperiksa > 0)
                    <h2>{{ $belumDiperiksa }} peserta menunggu pemeriksaan</h2>

                    <p>
                        Buka daftar peserta untuk melihat siapa yang perlu
                        diperiksa berikutnya.
                    </p>
                @else
                    <h2>Semua peserta sudah diperiksa</h2>
                    <p>Tinjau hasilnya di riwayat pemeriksaan.</p>
                @endif
            </div>

            <a
                href="{{ $belumDiperiksa > 0 || $totalPeserta === 0 ? route('peserta.index') : route('admin.riwayat') }}"
                class="btn btn-white"
            >
                {{ $belumDiperiksa > 0 || $totalPeserta === 0 ? 'Lihat Data Peserta' : 'Buka Riwayat' }}

                <span class="dot">
                    @include('partials.icon', ['name' => 'arrow-right'])
                </span>
            </a>
        </section>

        {{-- Banner Kartu --}}
        <section class="banner banner-kartu">
            <svg
                class="deco"
                viewBox="0 0 200 200"
                fill="none"
                stroke="#fff"
                stroke-width="6"
                aria-hidden="true"
            >
                <circle cx="100" cy="100" r="90"/>
                <circle cx="100" cy="100" r="62"/>
                <circle cx="100" cy="100" r="34"/>
            </svg>

            <div>
                @if ($totalKartu === 0)
                    <h2>Data kartu masih kosong</h2>
                    <p>Data kartu muncul setelah file Excel Format 2 diimpor.</p>
                @elseif ($belumDiperiksaKartu > 0)
                    <h2>{{ $belumDiperiksaKartu }} kartu menunggu pemeriksaan</h2>

                    <p>
                        Buka daftar kartu untuk melihat kartu yang perlu
                        diperiksa berikutnya.
                    </p>
                @else
                    <h2>Semua kartu sudah diperiksa</h2>
                    <p>Tinjau hasilnya di riwayat pemeriksaan.</p>
                @endif
            </div>

            <a
                href="{{ $belumDiperiksaKartu > 0 || $totalKartu === 0 ? route('kartu.index') : route('admin.riwayat') }}"
                class="btn btn-white"
            >
                {{ $belumDiperiksaKartu > 0 || $totalKartu === 0 ? 'Lihat Data Kartu' : 'Buka Riwayat' }}

                <span class="dot">
                    @include('partials.icon', ['name' => 'arrow-right'])
                </span>
            </a>
        </section>
    </div>

    {{-- CSS khusus dashboard --}}
    <style>
        /* ---------- Kotak ringkasan di hero ---------- */
        .dashboard-summary {
            position: relative;
            display: block;
            width: 100%;
            min-width: 0;
            min-height: 350px;
            box-sizing: border-box;
        }

        /* Saklar Peserta | Kartu */
        .dashboard-summary .summary-tabs {
            position: relative;
            z-index: 3;
            width: min(100%, 240px);
            margin: 0 0 14px;
        }

        .dashboard-summary .summary-tabs button {
            min-height: 34px;
            font-size: 13px;
        }

        .dashboard-summary .summary-panel {
            position: absolute;
            top: 54px;
            right: 0;
            bottom: 0;
            left: 0;
            transition: opacity .35s ease, transform .45s var(--ease), visibility .35s;
        }

        .dashboard-summary .summary-panel:not(.is-active) {
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            pointer-events: none;
        }

        .dashboard-summary .float-card {
            box-sizing: border-box;
            margin: 0;
            min-width: 0;
        }

        /* Kartu progres: kiri atas */
        .dashboard-summary .donut-card {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;

            width: 70%;
            min-height: 150px;
            padding: 22px;
        }

        /* Kartu hasil: kanan bawah */
        .dashboard-summary .split-card {
            position: absolute;
            top: 120px;
            right: 0;
            z-index: 2;

            width: 55%;
            min-height: 0;
            padding: 18px 20px;
        }

        .dashboard-summary .donut-info {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .dashboard-summary .donut-info small,
        .dashboard-summary .donut-info strong {
            display: block;
        }

        .dashboard-summary .donut-info strong {
            margin-top: 8px;
            line-height: 1.5;
        }

        .dashboard-summary .split-card h3 {
            margin: 0 0 14px;
            font-size: 15px;
            line-height: 1.4;
        }

        .dashboard-summary .split-row {
            margin-bottom: 12px;
            min-width: 0;
        }

        .dashboard-summary .split-row:last-child {
            margin-bottom: 0;
        }

        .dashboard-summary .split-label {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 7px;
            font-size: 14px;
        }

        .dashboard-summary .split-label span:first-child {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .dashboard-summary .meter {
            width: 100%;
            overflow: hidden;
        }

        /* ---------- Dua banner berdampingan ---------- */
        .banners {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-top: 22px;
        }

        .banners .banner {
            margin-top: 0;
        }

        /* Banner kartu berwarna biru (penanda Format 2) */
        .banner-kartu {
            background: linear-gradient(115deg, #0e2c6b 0%, #1b4ea3 62%, #2563eb 125%);
        }

        html[data-theme="dark"] .banner-kartu {
            background: linear-gradient(115deg, #0a1f4f 0%, #153b85 62%, #1d4fd8 125%);
        }

        .banner-kartu .btn-white {
            color: #1d4ed8;
        }

        .banner-kartu .btn-white:hover {
            background: #eef3fd;
            color: #1d4ed8;
        }

        .banner-kartu .btn-white .dot {
            background: #1d4ed8;
        }

        /* Laptop dan tablet */
        @media (max-width: 1100px) {
            .dashboard-summary {
                min-height: 370px;
            }

            .dashboard-summary .donut-card {
                width: 75%;
            }

            .dashboard-summary .split-card {
                top: 130px;
                width: 62%;
            }
        }

        @media (max-width: 900px) {
            .banners {
                grid-template-columns: 1fr;
            }
        }

        /* HP: kartu disusun rapi ke bawah, hanya panel aktif yang tampil */
        @media (max-width: 600px) {
            .dashboard-summary {
                min-height: 0;
            }

            .dashboard-summary .summary-panel {
                position: relative;
                top: auto;
                right: auto;
                bottom: auto;
                left: auto;
                display: none;
                transition: none;
            }

            .dashboard-summary .summary-panel.is-active {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .dashboard-summary .donut-card,
            .dashboard-summary .split-card {
                position: relative;
                top: auto;
                right: auto;
                left: auto;
                width: 100%;
                min-height: 0;
            }
        }
    </style>
@endsection

@push('scripts')
<script>
    (function () {
        var seg = document.querySelector('.summary-tabs');
        if (!seg) { return; }

        var tabs = seg.querySelectorAll('[role="tab"]');

        // Ulangi animasi lingkaran dan bar saat panel ditampilkan
        function restart(panel) {
            panel.querySelectorAll('.donut .bar, .meter i').forEach(function (el) {
                el.style.animation = 'none';
                void el.offsetWidth;
                el.style.animation = '';
            });
        }

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () {
                seg.style.setProperty('--i', index);

                tabs.forEach(function (t) {
                    var aktif = t === tab;
                    t.setAttribute('aria-selected', aktif ? 'true' : 'false');
                    document.getElementById(t.dataset.panel).classList.toggle('is-active', aktif);
                });

                restart(document.getElementById(tab.dataset.panel));
            });
        });
    })();
</script>
@endpush