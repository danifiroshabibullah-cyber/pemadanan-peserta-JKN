<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migrasi Database — Akses Dibatasi</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .box { background: #1e293b; border: 1px solid #334155; border-radius: 12px; max-width: 560px; width: 100%; padding: 32px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
        h1 { margin-top: 0; font-size: 1.5rem; color: #f59e0b; }
        p { line-height: 1.6; color: #cbd5e1; }
        code { background: #0f172a; padding: 4px 8px; border-radius: 6px; font-size: 0.9em; color: #38bdf8; word-break: break-all; }
        .btn { display: inline-block; background: #10b981; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-top: 16px; }
        .btn:hover { background: #059669; }
        .instructions { background: #0f172a; border-left: 4px solid #38bdf8; padding: 16px; border-radius: 4px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🔒 Akses Migrasi Database Dibatasi</h1>
        <p>Halaman ini digunakan untuk menjalankan migrasi dan pembuatan tabel otomatis ke database Neon PostgreSQL pada deployment Vercel.</p>
        
        <div class="instructions">
            <p><strong>Cara menjalankan migrasi:</strong></p>
            <p>Buka halaman ini dengan menambahkan parameter kunci:</p>
            <code>?key=neon-migrate-123</code>
            <p>atau gunakan nilai <code>APP_KEY</code> Anda dari konfigurasi environment Vercel:</p>
            <code>?key=YOUR_APP_KEY</code>
        </div>

        <p style="margin-top: 24px;">
            <a href="{{ url('/system/migrate?key=neon-migrate-123') }}" class="btn">Jalankan Migrasi dengan Kunci Bawaan</a>
        </p>
    </div>
</body>
</html>

