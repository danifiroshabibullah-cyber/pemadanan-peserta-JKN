<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PesertaKartu extends Model
{
    protected $table = 'peserta_kartus';

    protected $fillable = [
        'no_urut',
        'tanggal',
        'no_kartu',
        'nik',
        'nama',
        'instansi',
        'peny',
        'jawaban_tinggal_copas',
        'pisat',
        'status_aktif',
        'jns_kpst',
        'terdaftar',
        'tunggakan',
        'data_lain',
    ];

    protected function casts(): array
    {
        return [
            'data_lain' => 'array',
        ];
    }

    /**
     * Riwayat pemeriksaan kartu ini, yang terbaru di urutan pertama.
     */
    public function pemeriksaan(): HasMany
    {
        return $this->hasMany(PemeriksaanKartu::class, 'peserta_kartu_id')
            ->orderByDesc('diperiksa_pada')
            ->orderByDesc('id');
    }
}
