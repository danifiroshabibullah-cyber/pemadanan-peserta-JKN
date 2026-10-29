<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Peserta extends Model
{
    /** Awalan kode pengganti untuk peserta yang belum punya NIK. */
    public const PREFIX_TANPA_NIK = 'TANPA-NIK-';

    protected $fillable = [
        'no_urut',
        'sk_kemensos',
        'nik',
        'nama',
        'alamat',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'faskes',
        'keterangan',
        'hasil',
    ];

    public function periodes(): HasMany
    {
        return $this->hasMany(PesertaPeriode::class)->orderByDesc('periode');
    }

    public function pemeriksaan(): HasMany
    {
        return $this->hasMany(PemeriksaanPeserta::class);
    }

    public function pemeriksaanTerakhir(): HasOne
    {
        return $this->hasOne(PemeriksaanPeserta::class)->latestOfMany();
    }

    /** Apakah peserta ini belum punya NIK asli. */
    public function getTanpaNikAttribute(): bool
    {
        return str_starts_with((string) $this->nik, self::PREFIX_TANPA_NIK);
    }

    /** NIK untuk ditampilkan; kode pengganti disamarkan. */
    public function getNikTampilAttribute(): string
    {
        return $this->tanpa_nik ? '(belum ada NIK)' : (string) $this->nik;
    }

    public function scopeTanpaNik(Builder $query): Builder
    {
        return $query->where('nik', 'like', self::PREFIX_TANPA_NIK . '%');
    }

    public function scopeDenganNik(Builder $query): Builder
    {
        return $query->where('nik', 'not like', self::PREFIX_TANPA_NIK . '%');
    }
}