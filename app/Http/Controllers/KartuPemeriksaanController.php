<?php

namespace App\Http\Controllers;

use App\Models\PemeriksaanKartu;
use App\Models\PesertaKartu;
use Illuminate\Http\Request;

class KartuPemeriksaanController extends Controller
{
    public function create($id)
    {
        $kartu = PesertaKartu::findOrFail($id);

        $pemeriksaan = PemeriksaanKartu::where('peserta_kartu_id', $kartu->id)
            ->latest('id')
            ->first();

        return view('kartu.pemeriksaan', compact('kartu', 'pemeriksaan'));
    }

    public function store(Request $request, $id)
    {
        $kartu = PesertaKartu::findOrFail($id);

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

        PemeriksaanKartu::create([
            'peserta_kartu_id' => $kartu->id,
            'status' => $validated['status'],
            'catatan' => $validated['catatan'] ?? null,
            'diperiksa_oleh' => $validated['diperiksa_oleh'] ?? null,
            'diperiksa_pada' => now(),
        ]);

        return redirect()
            ->route('kartu.show', $kartu->id)
            ->with('success', 'Hasil pemeriksaan kartu berhasil disimpan.');
    }
}