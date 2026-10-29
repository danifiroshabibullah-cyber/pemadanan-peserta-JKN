<?php

namespace App\Http\Controllers;

use App\Models\PesertaKartu;

class KartuDetailController extends Controller
{
    /**
     * Menampilkan halaman detail satu Data Kartu beserta riwayat pemeriksaannya.
     */
    public function show(int $id)
    {
        $kartu = PesertaKartu::with('pemeriksaan')->findOrFail($id);

        return view('kartu.show', compact('kartu'));
    }
}