<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Menampilkan form login untuk Koordinator TA.
     */
    public function showLoginForm()
    {
        return view('auth.login'); // Pastikan file resources/views/auth/login.blade.php ADA
    }

    /**
     * Memproses login berdasarkan email atau username.
     */
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'login'    => 'required|string',        // login bisa berupa email/username
            'password' => 'required|string|min:6',  // password minimal 6 karakter
        ]);

        $loginInput = $request->login;
        $password   = $request->password;

        $login = false;

        // Coba autentikasi menggunakan email
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $login = Auth::attempt([
                'email' => $loginInput,
                'password' => $password,
            ]);
        }

        // Jika login email gagal, coba autentikasi menggunakan username
        if (! $login) {
            $login = Auth::attempt([
                'username' => $loginInput,
                'password' => $password,
            ]);
        }

        // Jika login berhasil
        if ($login) {
            $request->session()->regenerate(); // Regenerasi session untuk keamanan
            return redirect()->intended(route('poster.index')); // redirect ke halaman utama admin
        }

        // Jika login gagal
        return back()->withErrors([
            'login' => 'Email, Username, atau Password salah.',
        ])->withInput();
    }

    /**
     * Logout dan arahkan kembali ke halaman semua poster (publik).
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('poster.semua')->with('success', 'Anda berhasil logout.');
    }
}
