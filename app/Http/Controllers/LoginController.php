<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * LoginController — autentikasi sesi.
 *
 * Login memakai email + password, meregenerasi session untuk mencegah fixation,
 * dan menghormati parameter "ingat saya". Logout menghapus session + token CSRF.
 */
class LoginController extends Controller
{
    /** Tampilkan halaman login untuk tamu. */
    public function form()
    {
        return view('login');
    }

    /** Validasi kredensial, regenerasi session, lalu arahkan ke halaman tujuan. */
    public function login(Request $r)
    {
        $data = $r->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($data, $r->boolean('ingat'))) {
            $r->session()->regenerate();

            return redirect()->intended('/');
        }

        return back()->withErrors(['email' => 'Email atau kata sandi tidak cocok.'])->onlyInput('email');
    }

    /** Hapus autentikasi, session, dan token CSRF. */
    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect('/login');
    }
}
