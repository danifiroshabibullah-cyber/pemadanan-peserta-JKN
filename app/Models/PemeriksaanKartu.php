<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemeriksaanKartu extends Model
{
    protected $table = 'pemeriksaan_kartus';

    protected $fillable = [
        'peserta_kartu_id',
        'status',
        'catatan',
        'diperiksa_oleh',
        'diperiksa_pada',
    ];

    protected function casts(): array
    {
        return [
            'diperiksa_pada' => 'datetime',
        ];
    }

    public function kartu(): BelongsTo
    {
        return $this->belongsTo(PesertaKartu::class, 'peserta_kartu_id');
    }
}