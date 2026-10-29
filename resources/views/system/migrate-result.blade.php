<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Migrasi Database</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .box { background: #1e293b; border: 1px solid #334155; border-radius: 12px; max-width: 680px; width: 100%; padding: 32px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
        h1 { margin-top: 0; font-size: 1.6rem; display: flex; align-items: center; gap: 8px; }
        .success { color: #10b981; }
        .danger { color: #ef4444; }
        p { line-height: 1.6; color: #cbd5e1; }
        .info-card { background: #0f172a; border-radius: 8px; padding: 16px; margin: 16px 0; font-size: 0.95rem; }
        .info-card table { width: 100%; border-collapse: collapse; }
        .info-card td { padding: 6px 4px; }
        .info-card td:first-child { color: #94a3b8; width: 130px; }
        .info-card td:last-child { color: #f1f5f9; font-weight: 600; }
        pre { background: #020617; border: 1px solid #1e293b; border-radius: 8px; padding: 14px; font-size: 0.85rem; color: #38bdf8; overflow-x: auto; max-height: 250px; }
        .btn { display: inline-block; background: #10b981; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-top: 16px; text-align: center; }
        .btn:hover { background: #059669; }
        .credentials { background: #1e1b4b; border-left: 4px solid #6366f1; padding: 16px; border-radius: 4px; margin: 16px 0; }
        .credentials h3 { margin: 0 0 8px 0; font-size: 1rem; color: #a5b4fc; }
        .credentials ul { margin: 0; padding-left: 20px; color: #c7d2fe; font-size: 0.9rem; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="box">
        @if ($sukses)
            <h1 class="success">✅ Migrasi Database Berhasil!</h1>
            <p>Database telah berhasil terhubung dan semua tabel beserta data akun awal telah dibuat dengan sukses.</p>

            <div class="info-card">
                <table>
                    <tr><td>Driver DB</td><td>{{ strtoupper($infoKoneksi['driver'] ?? '-') }}</td></tr>
                    <tr><td>Host</td><td>{{ $infoKoneksi['host'] ?? '-' }}</td></tr>
                    <tr><td>Database</td><td>{{ $infoKoneksi['database'] ?? '-' }}</td></tr>
                    <tr><td>Status</td><td class="success">{{ $pesanKoneksi }}</td></tr>
                </table>
            </div>

            <div class="credentials">
                <h3>Akun Default yang Siap Digunakan:</h3>
                <ul>
                    <li><strong>Admin:</strong> <code>admin.pemadanan@gmail.com</code> | Kata Sandi: <code>admin1234</code></li>
                    <li><strong>Petugas (User):</strong> <code>petugas.pemadanan@gmail.com</code> | Kata Sandi: <code>petugas1234</code></li>
                </ul>
            </div>

            @if ($outputMigrate)
                <p><strong>Log Migrasi:</strong></p>
                <pre>{{ trim($outputMigrate) }}</pre>
            @endif

            @if ($outputSeed)
                <p><strong>Log Seeder:</strong></p>
                <pre>{{ trim($outputSeed) }}</pre>
            @endif

            <p style="text-align: center; margin-top: 24px;">
                <a href="{{ route('login') }}" class="btn">Masuk ke Halaman Login</a>
            </p>
        @else
            <h1 class="danger">❌ Migrasi Database Gagal</h1>
            <p>{{ $pesanKoneksi }}</p>

            <div class="info-card">
                <table>
                    <tr><td>Driver DB</td><td>{{ strtoupper($infoKoneksi['driver'] ?? '-') }}</td></tr>
                    <tr><td>Host</td><td>{{ $infoKoneksi['host'] ?? '-' }}</td></tr>
                    <tr><td>Database</td><td>{{ $infoKoneksi['database'] ?? '-' }}</td></tr>
                </table>
            </div>

            @if ($outputMigrate)
                <pre>{{ trim($outputMigrate) }}</pre>
            @endif

            <p style="color: #94a3b8; font-size: 0.9rem;">
                Pastikan variabel environment <code>DATABASE_URL</code> atau <code>DB_HOST</code>, <code>DB_USERNAME</code>, <code>DB_PASSWORD</code>, <code>DB_DATABASE</code>, dan <code>DB_SSLMODE=require</code> sudah disetel dengan benar di dashboard Vercel.
            </p>
        @endif
    </div>
</body>
</html>

