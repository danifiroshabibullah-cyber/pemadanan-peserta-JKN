<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PesertaPeriode extends Model
{
    protected $table = 'peserta_periodes';

    protected $fillable = [
        'peserta_id',
        'periode',
        'status',
        'segmen',
    ];

    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class);
    }

    /**
     * Label bulan yang mudah dibaca, contoh: "Sep-26".
     */
    public static function labelPeriode(string $periode): string
    {
        try {
            return Carbon::createFromFormat('!Y-m', $periode)->format('M-y');
        } catch (\Throwable $e) {
            return $periode;
        }
    }
}
