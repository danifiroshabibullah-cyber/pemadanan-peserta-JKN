<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Foto profil disimpan langsung di public/uploads/avatars,
     * sehingga tidak membutuhkan "php artisan storage:link".
     */
    private function folderAvatar(): string
    {
        return public_path('uploads/avatars');
    }

    private function hapusBerkasAvatar(?string $nama): void
    {
        if (!$nama) {
            return;
        }

        $nama = basename($nama);

        foreach ([
            $this->folderAvatar() . DIRECTORY_SEPARATOR . $nama,
            public_path('storage/avatars/' . $nama),
            storage_path('app/public/avatars/' . $nama),
        ] as $path) {
            if (File::isFile($path)) {
                @File::delete($path);
            }
        }
    }

    /**
     * Menampilkan halaman profil.
     */
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Menyimpan perubahan profil dan foto (Admin maupun Pengguna).
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->merge([
            'username' => strtolower(trim((string) $request->input('username'))),
        ]);

        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'nullable',
                'string',
                'min:3',
                'max:30',
                'alpha_dash:ascii',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'bio' => ['nullable', 'string', 'max:300'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        $data = $request->validate($rules, [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'username.min' => 'Nama pengguna minimal 3 karakter.',
            'username.max' => 'Nama pengguna maksimal 30 karakter.',
            'username.alpha_dash' => 'Nama pengguna hanya boleh berisi huruf, angka, strip (-), dan garis bawah (_).',
            'username.unique' => 'Nama pengguna ini sudah dipakai orang lain.',
            'avatar.image' => 'Berkas harus berupa gambar.',
            'avatar.mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'avatar.max' => 'Ukuran foto maksimal 2 MB.',
            'avatar.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya di bawah 2 MB.',
            'bio.max' => 'Biodata maksimal 300 karakter.',
        ]);

        $user->name = trim($data['name']);
        $user->username = ($data['username'] ?? '') !== '' ? $data['username'] : null;

        $bio = trim((string) ($data['bio'] ?? ''));
        $user->bio = $bio !== '' ? $bio : null;

        $oldAvatar = $user->avatar;
        $newAvatar = null;
        $avatarWarning = null;

        if ($request->hasFile('avatar')) {
            try {
                $file = $request->file('avatar');

                File::ensureDirectoryExists($this->folderAvatar());

                $newAvatar = $user->id . '-' . Str::random(20) . '.' . strtolower($file->extension());
                $file->move($this->folderAvatar(), $newAvatar);

                $user->avatar = $newAvatar;
            } catch (\Throwable $e) {
                $avatarWarning = 'Foto profil tidak dapat disimpan ke penyimpanan lokal pada lingkungan hosting ini. Data profil lainnya berhasil disimpan.';
            }
        }

        try {
            $user->save();
        } catch (\Throwable $e) {
            $this->hapusBerkasAvatar($newAvatar);

            throw $e;
        }

        if ($newAvatar) {
            $this->hapusBerkasAvatar($oldAvatar);
        }

        $response = redirect()
            ->route('profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');

        if ($avatarWarning) {
            $response->with('warning', $avatarWarning);
        }

        return $response;
    }

    /**
     * Menghapus foto profil.
     */
    public function destroyAvatar(Request $request)
    {
        $user = $request->user();
        $oldAvatar = $user->avatar;

        $user->avatar = null;
        $user->save();

        $this->hapusBerkasAvatar($oldAvatar);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Foto profil dihapus.');
    }

    /**
     * Mengubah kata sandi.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:12',
                'confirmed',
                'different:current_password',
            ],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.max' => 'Kata sandi baru maksimal 12 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sama.',
            'password.different' => 'Kata sandi baru harus berbeda dari yang lama.',
        ]);

        $request->user()->update([
            'password' => $request->input('password'),
        ]);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Kata sandi berhasil diubah.');
    }
}
