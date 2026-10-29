<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Menampilkan halaman registrasi pengguna.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Registrasi akun pengguna biasa.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'ends_with:@gmail.com',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:12',
                'confirmed',
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.ends_with' => 'Email harus menggunakan @gmail.com.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.max' => 'Kata sandi maksimal 12 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
        ]);

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Registrasi berhasil. Silakan login menggunakan akun yang sudah didaftarkan.'
            );
    }

    /**
     * Login untuk Admin DAN Pengguna lewat satu formulir.
     * Peran (admin / user) dibaca dari data akun, bukan dari tombol yang dipilih.
     */
    public function userAdminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
                'ends_with:@gmail.com',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:12',
            ],
        ], [
            'email.ends_with' => 'Email harus menggunakan @gmail.com.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.max' => 'Password maksimal 12 karakter.',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (
            !$user ||
            !in_array($user->role, ['admin', 'user'], true) ||
            !Hash::check($credentials['password'], $user->password)
        ) {
            return back()
                ->withErrors([
                    'email' => 'Email atau password tidak valid.',
                ])
                ->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Login Admin menggunakan akun yang dibuat secara manual.
     */
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
                'ends_with:@gmail.com',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:12',
            ],
        ], [
            'email.ends_with' => 'Email harus menggunakan @gmail.com.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.max' => 'Password maksimal 12 karakter.',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (
            !$user ||
            $user->role !== 'admin' ||
            !Hash::check($credentials['password'], $user->password)
        ) {
            return back()
                ->withErrors([
                    'email' => 'Email atau password Admin tidak valid.',
                ])
                ->with('login_role', 'admin')
                ->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Login pengguna yang sudah melakukan registrasi.
     */
    public function userLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
                'ends_with:@gmail.com',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:12',
            ],
        ], [
            'email.ends_with' => 'Email harus menggunakan @gmail.com.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.max' => 'Password maksimal 12 karakter.',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (
            !$user ||
            $user->role !== 'user' ||
            !Hash::check($credentials['password'], $user->password)
        ) {
            return back()
                ->withErrors([
                    'email' => 'Email atau password pengguna tidak valid. Pastikan akun sudah terdaftar.',
                ])
                ->with('login_role', 'user')
                ->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Keluar dari akun.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}