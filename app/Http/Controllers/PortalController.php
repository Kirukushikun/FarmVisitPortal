<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permit;

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

    public function userShowPermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();

        $permit->load([
            'farmLocation',
            'destinationLocation',
            'previousFarmLocation',
        ]);

        return view('user.permits.show', [
            'permit' => $permit,
        ]);
    }

    public function userScheduledPermits(Request $request): mixed
    {
        $user = $request->user();

        return view('user.permits.scheduled', [
            'user' => $user,
        ]);
    }

    public function userMyPermits(Request $request): mixed
    {
        $user = $request->user();

        return view('user.permits.my-permits', [
            'user' => $user,
        ]);
    }

    public function userCancelledPermits(Request $request): mixed
    {
        $user = $request->user();

        return view('user.permits.cancelled', [
            'user' => $user,
        ]);
    }

    public function completePermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();

        if ((int) ($user->user_type ?? 0) === 1) {
            abort(403);
        }

        if ($permit->status >= 2) {
            return redirect()->back()->with('error', 'Permit cannot be completed.');
        }

        $permit->update([
            'status' => 2, // Completed
            'completed_at' => now(),
            'received_by' => $user->id,
        ]);

        return redirect()->route('user.home')->with('success', 'Permit marked as completed successfully!');
    }

    public function cancelPermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();

        if ((int) ($user->user_type ?? 0) === 1) {
            abort(403);
        }

        if ($permit->status >= 3) {
            return redirect()->back()->with('error', 'Permit cannot be cancelled.');
        }

        $permit->update([
            'status' => 3, // Cancelled
            'received_by' => $user->id,
        ]);

        return redirect()->route('user.home')->with('success', 'Permit cancelled successfully!');
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

    public function adminPermits(Request $request): mixed
    {
        $user = $request->user();

        if ((int) ($user->user_type ?? 0) !== 1) {
            abort(403);
        }

        return view('admin.permits.index');
    }

    public function adminCreatePermit(Request $request): mixed
    {
        $user = $request->user();

        if ((int) ($user->user_type ?? 0) !== 1) {
            abort(403);
        }

        return view('admin.permits.create');
    }

    public function adminEditPermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();

        if ((int) ($user->user_type ?? 0) !== 1) {
            abort(403);
        }

        return view('admin.permits.edit');
    }

    public function adminShowPermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();

        if ((int) ($user->user_type ?? 0) !== 1) {
            abort(403);
        }

        $permit->load([
            'farmLocation',
            'destinationLocation',
            'previousFarmLocation',
        ]);

        return view('admin.permits.show', [
            'permit' => $permit,
        ]);
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
