<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Show the application's login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        // Validate the form data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('email', 'remember'));
        }

        // Get credentials
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Attempt to log the user in
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Ubah baris ini untuk selalu mengarahkan ke halaman dashboard
            return redirect()->route('home')->with('success', 'Login berhasil! Selamat datang kembali.');
        }

        // If unsuccessful, redirect back with error
        return redirect()->back()
            ->withErrors(['email' => 'Email atau password tidak valid. Silakan coba lagi atau hubungi administrator.'])
            ->withInput($request->only('email', 'remember'));
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Anda telah logout dengan aman.');
    }
}
