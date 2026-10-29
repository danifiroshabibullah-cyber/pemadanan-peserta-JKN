<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use App\Models\PemeriksaanKartu;
use App\Models\PemeriksaanPeserta;
use App\Models\PesertaKartu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Halaman Pengguna tidak memakai dashboard rekap Admin.
        if ($user->role === 'user') {
            return view('dashboard-pengguna');
        }

        // Rekapitulasi khusus Admin.
        $totalPeserta = Peserta::count();

        // Menghitung peserta yang pernah diperiksa.
        $sudahDiperiksa = PemeriksaanPeserta::query()
            ->distinct('peserta_id')
            ->count('peserta_id');

        $belumDiperiksa = max(
            0,
            $totalPeserta - $sudahDiperiksa
        );

        // Menghitung hasil pemeriksaan terakhir setiap peserta.
        $pemeriksaanTerakhir = PemeriksaanPeserta::query()
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('pemeriksaan_pesertas')
                    ->groupBy('peserta_id');
            });

        $sesuai = (clone $pemeriksaanTerakhir)
            ->where('status', 'Sesuai')
            ->count();

        $perluDitindaklanjuti = (clone $pemeriksaanTerakhir)
            ->where('status', 'Perlu ditindaklanjuti')
            ->count();

        // Rekapitulasi Data Kartu.
        $totalKartu = PesertaKartu::count();

        $kartuAktif = PesertaKartu::query()
            ->where('status_aktif', 'like', '%aktif%')
            ->where('status_aktif', 'not like', '%tidak%')
            ->where('status_aktif', 'not like', '%non%')
            ->count();

        $kartuTidakAktif = PesertaKartu::query()
            ->where(function ($query) {
                $query->where('status_aktif', 'like', '%tidak%')
                    ->orWhere('status_aktif', 'like', '%non%');
            })
            ->count();

        // Data Kartu yang NIK-nya juga ada di Data Peserta.
        $tabelKartu = (new PesertaKartu())->getTable();

        $kartuCocok = PesertaKartu::query()
            ->whereExists(function ($sub) use ($tabelKartu) {
                $sub->select(DB::raw(1))
                    ->from('pesertas')
                    ->whereColumn('pesertas.nik', $tabelKartu . '.nik');
            })
            ->count();

        $kartuBelumCocok = max(0, $totalKartu - $kartuCocok);

        // Pemeriksaan Data Kartu (bernilai 0 jika tabelnya belum dimigrasi).
        $sudahDiperiksaKartu = 0;
        $kartuSesuai = 0;
        $kartuPerluTindak = 0;

        if (Schema::hasTable('pemeriksaan_kartus')) {
            $sudahDiperiksaKartu = PemeriksaanKartu::query()
                ->distinct()
                ->count('peserta_kartu_id');

            $pemeriksaanKartuTerakhir = PemeriksaanKartu::query()
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(id)')
                        ->from('pemeriksaan_kartus')
                        ->groupBy('peserta_kartu_id');
                });

            $kartuSesuai = (clone $pemeriksaanKartuTerakhir)
                ->where('status', 'Sesuai')
                ->count();

            $kartuPerluTindak = (clone $pemeriksaanKartuTerakhir)
                ->where('status', 'Perlu ditindaklanjuti')
                ->count();
        }

        $belumDiperiksaKartu = max(0, $totalKartu - $sudahDiperiksaKartu);

        return view('dashboard', compact(
            'totalPeserta',
            'sudahDiperiksa',
            'belumDiperiksa',
            'sesuai',
            'perluDitindaklanjuti',
            'totalKartu',
            'kartuAktif',
            'kartuTidakAktif',
            'kartuCocok',
            'kartuBelumCocok',
            'sudahDiperiksaKartu',
            'belumDiperiksaKartu',
            'kartuSesuai',
            'kartuPerluTindak'
        ));
    }
}