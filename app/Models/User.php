<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'avatar',
        'bio',
        'password',
        'role',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Alamat foto profil, atau null jika belum ada / berkasnya hilang.
     * Foto lama yang masih tersimpan di public/storage/avatars tetap dikenali.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        $nama = basename($this->avatar);

        foreach (['uploads/avatars', 'storage/avatars'] as $folder) {
            $path = public_path($folder . '/' . $nama);

            if (is_file($path)) {
                return asset($folder . '/' . $nama) . '?v=' . filemtime($path);
            }
        }

        return null;
    }

    /**
     * Inisial nama untuk avatar bawaan (maksimal dua huruf).
     */
    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $letters = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $letters !== '' ? $letters : 'U';
    }
}