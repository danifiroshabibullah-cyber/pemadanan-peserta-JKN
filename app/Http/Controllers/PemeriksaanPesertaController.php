<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use App\Models\PemeriksaanPeserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PemeriksaanPesertaController extends Controller
{
    public function create($id)
    {
        $peserta = Peserta::findOrFail($id);

        $pemeriksaan = PemeriksaanPeserta::where(
            'peserta_id',
            $peserta->id
        )->latest()->first();

        return view(
            'peserta.pemeriksaan',
            compact('peserta', 'pemeriksaan')
        );
    }

    public function store(Request $request, $id)
    {
        $peserta = Peserta::findOrFail($id);

        $validated = $request->validate([
            'status' => [
                'required',
                'in:Sesuai,Perlu ditindaklanjuti',
            ],
            'catatan' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'diperiksa_oleh' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $petugas = trim((string) ($validated['diperiksa_oleh'] ?? ''));

        DB::transaction(function () use ($peserta, $validated, $petugas) {
            PemeriksaanPeserta::create([
                'peserta_id' => $peserta->id,
                'status' => $validated['status'],
                'catatan' => $validated['catatan'] ?? null,
                'diperiksa_oleh' => $petugas !== '' ? $petugas : auth()->user()->name,
                'diperiksa_pada' => now(),
            ]);
        });

        return redirect()
            ->route('peserta.show', $peserta->id)
            ->with('success', 'Hasil pemeriksaan berhasil disimpan.');
    }
}