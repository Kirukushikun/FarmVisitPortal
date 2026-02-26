<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function userHome(Request $request): mixed
    {
        $user = $request->user();

        if ((int) ($user->user_type ?? 0) === 1 && (string) $request->session()->get('ui_mode') !== 'user') {
            return redirect()->route('admin.home');
        }

        return view('user.home');
    }

    public function userChangePassword(Request $request): mixed
    {
        $user = $request->user();

        if ((int) ($user->user_type ?? 0) === 1) {
            abort(403);
        }

        return view('auth.change-password-page');
    }

    public function adminHome(Request $request): mixed
    {
        $user = $request->user();

        if ((int) ($user->user_type ?? 0) !== 1) {
            abort(403);
        }

        return view('admin.home');
    }

    public function adminUsers(Request $request): mixed
    {
        $user = $request->user();

        if ((int) ($user->user_type ?? 0) !== 1) {
            abort(403);
        }

        return view('admin.users');
    }

    public function adminLocations(Request $request): mixed
    {
        $user = $request->user();

        if ((int) ($user->user_type ?? 0) !== 1) {
            abort(403);
        }

        return view('admin.locations');
    }

    public function adminChangePassword(Request $request): mixed
    {
        $user = $request->user();

        if ((int) ($user->user_type ?? 0) !== 1) {
            abort(403);
        }

        return view('auth.change-password-page');
    }
}
