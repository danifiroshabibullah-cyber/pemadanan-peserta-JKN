<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemeriksaanPeserta extends Model
{
    protected $table = 'pemeriksaan_pesertas';

    protected $fillable = [
        'peserta_id',
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

    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class);
    }
}