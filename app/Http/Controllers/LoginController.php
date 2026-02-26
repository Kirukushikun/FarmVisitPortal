<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:user,admin'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['username' => $validated['username'], 'password' => $validated['password']])) {
            return back()->withErrors(['username' => 'Invalid credentials.'])->onlyInput('username');
        }

        $request->session()->regenerate();

        $user = $request->user();
        if (($user->is_disabled ?? false) === true) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['username' => 'This account is disabled.'])->onlyInput('username');
        }
        $isAdmin = (int) ($user->user_type ?? 0) === 1;
        $expectedAdmin = $validated['role'] === 'admin';

        if ($expectedAdmin !== $isAdmin) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'role' => $expectedAdmin ? 'This account is not an admin.' : 'This account is not a user.',
            ])->onlyInput('username');
        }

        return redirect()->route($expectedAdmin ? 'admin.home' : 'user.home');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
