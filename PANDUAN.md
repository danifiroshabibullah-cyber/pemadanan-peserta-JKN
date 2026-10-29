# Pemadanan Peserta & Data Kartu — Panduan Lengkap

---

## 1. Menjalankan di Lokal (Laragon / XAMPP)

1. Pastikan Apache & MySQL berjalan di Laragon/XAMPP.
2. Buka terminal di folder proyek, lalu jalankan:
   ```bash
   php artisan migrate
   php artisan serve
   ```
3. Buka browser di [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## 2. Deploy ke Vercel dengan Database Neon PostgreSQL

Proyek ini telah dikonfigurasi penuh agar kompatibel dengan lingkungan serverless Vercel dan database PostgreSQL Neon.

### Langkah-langkah di Vercel:

1. **Push kode proyek ke GitHub** (pastikan file `vercel.json` dan folder `api/` ikut ter-push).
2. Di Dashboard **Vercel** (`Project Settings -> Environment Variables`), tambahkan variabel berikut:
   * `APP_NAME` : `Pemadanan Peserta`
   * `APP_ENV` : `production`
   * `APP_DEBUG` : `false`
   * `APP_KEY` : `base64:dJ7OQwZ2BWdUVI2Jd+NpY19NCXtix0kRXedRXQCRnUo=` *(atau hasil dari `php artisan key:generate --show`)*
   * `APP_URL` : `https://nama-proyek-anda.vercel.app`
   * `DB_CONNECTION` : `pgsql`
   * `DB_SSLMODE` : `require`
   * `DATABASE_URL` : *(Salin Connection String dari Dashboard Neon Anda, contoh: `postgresql://neondb_owner:password@ep-xyz.aws.neon.tech/neondb?sslmode=require`)*
   * `SESSION_DRIVER` : `cookie`

3. **Deploy / Redeploy** di Vercel.

4. **Jalankan Migrasi & Buat Akun Awal di Neon:**
   Setelah website aktif di Vercel, buka URL berikut di browser Anda:
   ```
   https://nama-proyek-anda.vercel.app/system/migrate?key=neon-migrate-123
   ```
   *Halaman ini akan secara otomatis membuat semua tabel yang dibutuhkan di database Neon dan membuat akun awal.*

   *Atau alternatif lain, Anda bisa menjalankan migrasi langsung dari terminal lokal Anda dengan memasukkan `DATABASE_URL` Neon ke file `.env` lokal lalu menjalankan `php artisan migrate --force`.*

---

## 3. Akun Login Default

Setelah migrasi selesai, Anda dapat langsung login menggunakan:

| Peran | Email | Kata Sandi |
|---|---|---|
| **Admin** | `admin.pemadanan@gmail.com` | `admin1234` |
| **Petugas (User)** | `petugas.pemadanan@gmail.com` | `petugas1234` |

---

## 4. Format Excel Data Kartu (Format 2)

Kolom Excel yang dibaca dan diekspor sesuai tampilan:

| No | Tanggal | NO. KARTU | NIK | NAMA | INSTANSI | Penyelenggara | Jawaban Tinggal Copas | PISAT | Status Aktif | Jns Kpst | Terdaftar | Tunggakan |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | 29/10/2026 | 00013936084 | 3578190100000001 | MOCH FA | Dinas Per | ASN | Data yang bersangkutan telah aktif | Peserta | AKTIF | PEGAWAI | JAMKESDA | 0 |

* Template resmi dapat langsung diunduh melalui tombol **"Unduh Format Excel"** di menu **Import Excel -> Data Kartu**.
* Kolom **Tanggal** berada di posisi kolom B (tepat setelah No dan sebelum NO. KARTU).

---

## 5. Format Excel Data Peserta (Format 1)

`NO | SK KEMENSOS | NIK | NAMA | ALAMAT | KEC_NAME | KEL_NAME | NO_RW | NO_RT | KETERANGAN | NAMA FASKES | Sep-26 | SEKMEN | Oct-26 | SEKMEN | hasil`
