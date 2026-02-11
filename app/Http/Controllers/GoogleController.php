<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Exception;

class GoogleController extends Controller
{
    /**
     * Mengarahkan pengguna ke halaman login Google
     */
    public function redirectToGoogle()
    {
        // Memaksa pilihan akun muncul setiap klik
        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Menangani callback dari Google
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Mencari user berdasarkan email yang masuk
            $user = User::where('email', $googleUser->email)->first();

            if($user){
                // LOGIN MANUAL dengan Remember Me aktif (true)
                Auth::login($user, true);
                
                // Regenerasi session secara instan agar sesi dikenali middleware auth
                request()->session()->regenerate();
                
                // REDIRECT LANGSUNG ke URL '/dashboard'
                return redirect()->to('/dashboard')->with('success', 'Login berhasil sebagai ' . $user->role);
            } else {
                // Inilah alasan Anda terlempar: Email Google Anda tidak ada di database!
                return redirect()->to('/login')->with('error', 'Akses ditolak! Email ' . $googleUser->email . ' belum terdaftar di sistem.');
            }

        } catch (Exception $e) {
            return redirect()->to('/login')->with('error', 'Terjadi kesalahan sistem login Google.');
        }
    }
}