{{-- Pilihan format import + gaya label Format 1 (hijau) dan Format 2 (biru) --}}
<style>
    .import-tabs { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px; margin-bottom: 22px; max-width: 760px; }
    .import-tabs a {
        display: flex; flex-direction: column; gap: 4px;
        padding: 14px 18px; border-radius: 16px;
        border: 2px solid var(--line); background: var(--surface);
        color: var(--ink); text-decoration: none;
        transition: transform .2s var(--ease), border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
    }
    .import-tabs a:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
    .import-tabs a b { font-size: 15px; font-weight: 800; }
    .import-tabs a small { color: var(--muted); font-size: 12px; font-weight: 600; }
    .import-tabs a.tab-f1:hover, .import-tabs a.tab-f1[aria-current="page"] { border-color: #16a34a; }
    .import-tabs a.tab-f2:hover, .import-tabs a.tab-f2[aria-current="page"] { border-color: #2563eb; }
    .import-tabs a.tab-f1[aria-current="page"] { background: rgba(22, 163, 74, .10); }
    .import-tabs a.tab-f2[aria-current="page"] { background: rgba(37, 99, 235, .10); }
    .import-tabs a.tab-f1[aria-current="page"] b { color: #16a34a; }
    .import-tabs a.tab-f2[aria-current="page"] b { color: #2563eb; }
    html[data-theme="dark"] .import-tabs a.tab-f1[aria-current="page"] b { color: #4ade80; }
    html[data-theme="dark"] .import-tabs a.tab-f2[aria-current="page"] b { color: #93b4ff; }

    .fmt-badge {
        display: inline-block; padding: 4px 12px; border-radius: 999px;
        font-size: 12px; font-weight: 800; letter-spacing: .02em; margin-bottom: 8px;
    }
    .fmt-badge.f1 { background: rgba(22, 163, 74, .14); color: #15803d; }
    .fmt-badge.f2 { background: rgba(37, 99, 235, .14); color: #1d4ed8; }
    html[data-theme="dark"] .fmt-badge.f1 { color: #4ade80; }
    html[data-theme="dark"] .fmt-badge.f2 { color: #93b4ff; }

    .hasil-box { border-left: 6px solid var(--line); }
    .hasil-box.f1 { border-left-color: #16a34a; }
    .hasil-box.f2 { border-left-color: #2563eb; }
    .hasil-box p.isi { font-size: 15px; font-weight: 600; line-height: 1.6; }
</style>

<nav class="import-tabs" aria-label="Pilih format import">
    <a class="tab-f1" href="{{ route('import.peserta') }}" @if (request()->routeIs('import.peserta*')) aria-current="page" @endif>
        <b>Format 1: Data Peserta</b>
        <small>NIK, SK Kemensos, status per bulan (Sep-26, Oct-26)</small>
    </a>
    <a class="tab-f2" href="{{ route('import.kartu') }}" @if (request()->routeIs('import.kartu*')) aria-current="page" @endif>
        <b>Format 2: Data Kartu</b>
        <small>No. Kartu, Instansi, Status Aktif, Tunggakan</small>
    </a>
</nav>