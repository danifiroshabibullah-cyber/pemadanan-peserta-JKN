<?php

namespace App\Http\Controllers;

use App\Models\PemeriksaanKartu;
use App\Models\PemeriksaanPeserta;
use App\Models\Peserta;
use App\Models\PesertaKartu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RiwayatPemeriksaanController extends Controller
{
    /**
     * Riwayat pemeriksaan gabungan: Data Peserta dan Data Kartu.
     */
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $jenis = (string) $request->input('jenis', 'semua');

        if (! in_array($jenis, ['semua', 'peserta', 'kartu'], true)) {
            $jenis = 'semua';
        }

        $tabelPeserta = (new Peserta())->getTable();
        $tabelPeriksaPeserta = (new PemeriksaanPeserta())->getTable();
        $tabelKartu = (new PesertaKartu())->getTable();
        $tabelPeriksaKartu = (new PemeriksaanKartu())->getTable();

        $adaTabelKartu = Schema::hasTable($tabelPeriksaKartu);
        $like = '%' . $keyword . '%';

        // ---------- Pemeriksaan Data Peserta ----------
        $pesertaQ = DB::table($tabelPeriksaPeserta . ' as p')
            ->leftJoin($tabelPeserta . ' as t', 't.id', '=', 'p.peserta_id')
            ->selectRaw(
                "'peserta' as jenis, p.id as id, p.peserta_id as target_id, "
                . 't.nama as nama, t.nik as nik, t.sk_kemensos as nomor, '
                . 'p.status as status, p.catatan as catatan, '
                . 'p.diperiksa_oleh as diperiksa_oleh, p.diperiksa_pada as diperiksa_pada'
            );

        if ($keyword !== '') {
            $pesertaQ->where(function ($q) use ($like) {
                $q->where('p.status', 'like', $like)
                    ->orWhere('p.catatan', 'like', $like)
                    ->orWhere('p.diperiksa_oleh', 'like', $like)
                    ->orWhere('t.nama', 'like', $like)
                    ->orWhere('t.nik', 'like', $like)
                    ->orWhere('t.sk_kemensos', 'like', $like);
            });
        }

        // ---------- Pemeriksaan Data Kartu ----------
        $kartuQ = null;

        if ($adaTabelKartu) {
            // Hanya kolom yang benar-benar ada di tabel kartu yang dipakai.
            $kolomKartu = [];

            foreach (['nama', 'nik', 'no_kartu'] as $kolom) {
                if (Schema::hasColumn($tabelKartu, $kolom)) {
                    $kolomKartu[$kolom] = 'k.' . $kolom;
                }
            }

            $kartuQ = DB::table($tabelPeriksaKartu . ' as p')
                ->leftJoin($tabelKartu . ' as k', 'k.id', '=', 'p.peserta_kartu_id')
                ->selectRaw(
                    "'kartu' as jenis, p.id as id, p.peserta_kartu_id as target_id, "
                    . ($kolomKartu['nama'] ?? 'CAST(NULL AS VARCHAR)') . ' as nama, '
                    . ($kolomKartu['nik'] ?? 'CAST(NULL AS VARCHAR)') . ' as nik, '
                    . ($kolomKartu['no_kartu'] ?? 'CAST(NULL AS VARCHAR)') . ' as nomor, '
                    . 'p.status as status, p.catatan as catatan, '
                    . 'p.diperiksa_oleh as diperiksa_oleh, p.diperiksa_pada as diperiksa_pada'
                );

            if ($keyword !== '') {
                $kartuQ->where(function ($q) use ($like, $kolomKartu) {
                    $q->where('p.status', 'like', $like)
                        ->orWhere('p.catatan', 'like', $like)
                        ->orWhere('p.diperiksa_oleh', 'like', $like);

                    foreach ($kolomKartu as $kolom) {
                        $q->orWhere($kolom, 'like', $like);
                    }
                });
            }
        }

        // ---------- Jumlah per jenis (untuk tab) ----------
        $jumlah = [
            'peserta' => (clone $pesertaQ)->count(),
            'kartu' => $kartuQ ? (clone $kartuQ)->count() : 0,
        ];
        $jumlah['semua'] = $jumlah['peserta'] + $jumlah['kartu'];

        // ---------- Gabungkan sesuai tab yang dipilih ----------
        $pakaiPeserta = $jenis !== 'kartu';
        $pakaiKartu = $jenis !== 'peserta' && $kartuQ !== null;

        if (! $pakaiPeserta && ! $pakaiKartu) {
            // Tab Kartu dipilih tetapi tabel pemeriksaan kartu belum ada.
            $pesertaQ->whereRaw('1 = 0');
            $pakaiPeserta = true;
        }

        if ($pakaiPeserta && $pakaiKartu) {
            $gabung = $pesertaQ->unionAll($kartuQ);
        } elseif ($pakaiKartu) {
            $gabung = $kartuQ;
        } else {
            $gabung = $pesertaQ;
        }

        $riwayat = DB::query()
            ->fromSub($gabung, 'r')
            ->orderByDesc('diperiksa_pada')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.riwayat', compact('riwayat', 'keyword', 'jenis', 'jumlah'));
    }
}