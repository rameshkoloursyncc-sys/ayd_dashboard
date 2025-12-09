<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $request->session()->regenerate();

            $user = Auth::user();

            switch ($user->role) {
                case 'super_admin':
                    return redirect()->intended('/superadmin/pharma-companies');
                case 'pharma_admin':
                    return redirect()->intended('/pharma-admin/my-company');
                case 'medical_executive':
                    return redirect()->intended('/medical-executive/doctors');
                case 'doctor':
                    return redirect()->intended('/pharma-dashboard'); // Assuming doctors go to the general pharma dashboard for now
                default:
                    return redirect()->intended('/');
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
