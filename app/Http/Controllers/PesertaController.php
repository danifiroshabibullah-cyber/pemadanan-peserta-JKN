<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use App\Models\PemeriksaanPeserta;
use App\Models\PesertaPeriode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PesertaController extends Controller
{
    /**
     * Daftar peserta, pencarian, filter kecamatan, dan filter NIK.
     */
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $kecamatan = trim((string) $request->input('kecamatan', ''));
        $filterNik = (string) $request->input('nik_status', '');

        if (!in_array($filterNik, ['ada', 'tanpa'], true)) {
            $filterNik = '';
        }

        // Jika pengguna mengetik "tanpa nik" atau "belum ada nik".
        if (preg_match('/^(tanpa nik|belum ada nik)$/i', $keyword)) {
            $filterNik = 'tanpa';
            $keyword = '';
        }

        $query = Peserta::query()->with('pemeriksaanTerakhir');

        if ($keyword !== '') {
            $like = '%' . addcslashes($keyword, '%_\\') . '%';

            $query->where(function ($q) use ($like) {
                $q->where('nama', 'like', $like)
                    ->orWhere('nik', 'like', $like)
                    ->orWhere('sk_kemensos', 'like', $like)
                    ->orWhere('alamat', 'like', $like)
                    ->orWhere('kelurahan', 'like', $like)
                    ->orWhere('kecamatan', 'like', $like)
                    ->orWhere('faskes', 'like', $like)
                    ->orWhere('keterangan', 'like', $like)
                    ->orWhere('hasil', 'like', $like);
            });
        }

        if ($kecamatan !== '') {
            $query->where('kecamatan', $kecamatan);
        }

        if ($filterNik === 'tanpa') {
            $query->tanpaNik();
        } elseif ($filterNik === 'ada') {
            $query->denganNik();
        }

        $peserta = $query
            ->orderByRaw('CASE WHEN no_urut IS NULL THEN 1 ELSE 0 END')
            ->orderBy('no_urut')
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        // Ambil dua periode terbaru yang tersedia.
        $periodeAktif = PesertaPeriode::query()
            ->select('periode')
            ->distinct()
            ->orderByDesc('periode')
            ->limit(2)
            ->pluck('periode')
            ->sort()
            ->values();

        // Ambil data periode hanya untuk peserta pada halaman aktif.
        $dataPeriode = PesertaPeriode::query()
            ->whereIn('peserta_id', $peserta->pluck('id'))
            ->whereIn('periode', $periodeAktif)
            ->get()
            ->groupBy('peserta_id');

        $daftarKecamatan = Peserta::query()
            ->whereNotNull('kecamatan')
            ->where('kecamatan', '!=', '')
            ->distinct()
            ->orderBy('kecamatan')
            ->pluck('kecamatan');

        $totalSemua = Peserta::count();

        return view('peserta.index', compact(
            'peserta',
            'keyword',
            'kecamatan',
            'filterNik',
            'totalSemua',
            'periodeAktif',
            'dataPeriode',
            'daftarKecamatan'
        ));
    }

    /**
     * Detail peserta: identitas, periode, dan riwayat pemeriksaan.
     */
    public function show($id)
    {
        $peserta = Peserta::with([
            'periodes',
            'pemeriksaan' => function ($q) {
                $q->orderByDesc('diperiksa_pada')
                    ->orderByDesc('id');
            },
        ])->findOrFail($id);

        return view('peserta.show', compact('peserta'));
    }

    /**
     * Alias untuk kompatibilitas dengan route yang menggunakan method detail.
     */
    public function detail($id)
    {
        return $this->show($id);
    }

    /**
     * Menghapus seluruh data peserta Format 1 beserta periode
     * dan riwayat pemeriksaannya.
     */
    public function destroyAll(Request $request)
    {
        $request->merge([
            'konfirmasi' => strtoupper(
                trim((string) $request->input('konfirmasi'))
            ),
        ]);

        $request->validate([
            'konfirmasi' => ['required', 'in:HAPUS'],
        ], [
            'konfirmasi.required' =>
                'Ketik HAPUS untuk mengonfirmasi penghapusan.',
            'konfirmasi.in' =>
                'Konfirmasi tidak sesuai. Ketik HAPUS untuk melanjutkan.',
        ]);

        $total = Peserta::count();

        DB::transaction(function () {
            PemeriksaanPeserta::query()->delete();
            PesertaPeriode::query()->delete();
            Peserta::query()->delete();
        });

        // Reset nomor otomatis jika menggunakan MySQL.
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement(
                    'ALTER TABLE pemeriksaan_pesertas AUTO_INCREMENT = 1'
                );

                DB::statement(
                    'ALTER TABLE peserta_periodes AUTO_INCREMENT = 1'
                );

                DB::statement(
                    'ALTER TABLE pesertas AUTO_INCREMENT = 1'
                );
            } catch (Throwable $e) {
                // Data sudah dihapus; reset nomor otomatis tidak wajib.
                report($e);
            }
        }

        return redirect()
            ->route('peserta.index')
            ->with(
                'success',
                number_format($total, 0, ',', '.') .
                ' data peserta beserta riwayatnya berhasil dihapus.'
            );
    }
}