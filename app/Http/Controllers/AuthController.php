<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showUserLogin(Request $request): mixed
    {
        $locationId = $request->query('location');
        if ($locationId !== null) {
            $request->session()->put('selected_location_id', (int) $locationId);
        }

        $request->session()->put('ui_mode', 'user');

        $selectedLocationId = (int) $request->session()->get('selected_location_id', 0);
        $selectedLocationName = Location::whereKey($selectedLocationId)->value('name');

        return view('auth.login', [
            'forcedRole' => 'user',
            'selectedLocationName' => $selectedLocationName,
        ]);
    }

    public function showAdminLogin(Request $request): mixed
    {
        $request->session()->forget('selected_location_id');
        $request->session()->forget('ui_mode');

        return view('auth.login', [
            'forcedRole' => 'admin',
            'selectedLocationName' => null,
        ]);
    }

    public function login(Request $request): RedirectResponse
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

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
