<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permit;

class PortalController extends Controller
{
    private function isAdminType($user): bool
    {
        return in_array((int) ($user->user_type ?? 0), [1, 2], true);
    }

    private function isSuperAdminType($user): bool
    {
        return (int) ($user->user_type ?? 0) === 2;
    }

    public function userHome(Request $request): mixed
    {
        $user = $request->user();

        if ($this->isAdminType($user) && (string) $request->session()->get('ui_mode') !== 'user') {
            return redirect()->route('admin.home');
        }
        return view('user.home');
    }

    public function acceptPermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();

        $isAdmin = $this->isAdminType($user);
        $userFarmLocationId = (int) ($user->farm_location_id ?? 0);
        $permitFarmLocationId = (int) ($permit->farm_location_id ?? 0);

        if (! $isAdmin) {
            if ($userFarmLocationId <= 0 || $permitFarmLocationId <= 0 || $userFarmLocationId !== $permitFarmLocationId) {
                abort(403);
            }
        }

        if ((int) ($permit->status ?? 0) !== 1) {
            return redirect()->back()->with('error', 'Permit cannot be accepted.');
        }

        if ((int) ($permit->received_by ?? 0) !== 0) {
            if ((int) ($permit->received_by ?? 0) === (int) ($user->id ?? 0)) {
                return redirect()->back()->with('success', 'Permit already accepted.');
            }

            return redirect()->back()->with('error', 'Permit has already been accepted by another user.');
        }

        $permit->update([
            'received_by' => (int) ($user->id ?? 0),
        ]);

        return redirect()->back()->with('success', 'Permit accepted successfully!');
    }

    public function userChangePassword(Request $request): mixed
    {
        $user = $request->user();

        if ($this->isAdminType($user)) {
            return redirect()->route('admin.change-password');
        }

        return view('auth.change-password-page');
    }

    public function userShowPermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();

        $isAdmin = $this->isAdminType($user);
        $userFarmLocationId = (int) ($user->farm_location_id ?? 0);
        $permitFarmLocationId = (int) ($permit->farm_location_id ?? 0);

        $canView = $isAdmin
            || ((int) ($permit->created_by ?? 0) === (int) ($user->id ?? 0))
            || ((int) ($permit->received_by ?? 0) === (int) ($user->id ?? 0))
            || ($userFarmLocationId > 0 && $permitFarmLocationId > 0 && $userFarmLocationId === $permitFarmLocationId);

        if (! $canView) {
            abort(403);
        }

        $permit->load([
            'farmLocation',
            'previousFarmLocation',
            'photos',
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

        $isAdmin = $this->isAdminType($user);
        if (! $isAdmin
            && (int) ($permit->created_by ?? 0) !== (int) ($user->id ?? 0)
            && (int) ($permit->received_by ?? 0) !== (int) ($user->id ?? 0)) {
            abort(403);
        }

        if ((int) ($permit->status ?? 0) !== 1) {
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

        $isAdmin = $this->isAdminType($user);
        if (! $isAdmin
            && (int) ($permit->created_by ?? 0) !== (int) ($user->id ?? 0)
            && (int) ($permit->received_by ?? 0) !== (int) ($user->id ?? 0)) {
            abort(403);
        }

        if ((int) ($permit->status ?? 0) !== 1) {
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

        if (! $this->isAdminType($user)) {
            abort(403);
        }

        return view('admin.home');
    }

    public function adminUsers(Request $request): mixed
    {
        $user = $request->user();

        if (! $this->isAdminType($user)) {
            abort(403);
        }

        return view('admin.users');
    }

    public function adminAdmins(Request $request): mixed
    {
        $user = $request->user();

        if (! $this->isSuperAdminType($user)) {
            abort(403);
        }

        return view('admin.admins');
    }

    public function adminLocations(Request $request): mixed
    {
        $user = $request->user();

        if (! $this->isAdminType($user)) {
            abort(403);
        }

        return view('admin.locations');
    }

    public function adminPermits(Request $request): mixed
    {
        $user = $request->user();

        if (! $this->isAdminType($user)) {
            abort(403);
        }

        return view('admin.permits.index');
    }

    public function adminCreatePermit(Request $request): mixed
    {
        $user = $request->user();

        if (! $this->isAdminType($user)) {
            abort(403);
        }

        return view('admin.permits.create');
    }

    public function adminEditPermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();

        if (! $this->isAdminType($user)) {
            abort(403);
        }

        return view('admin.permits.edit');
    }

    public function adminShowPermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();

        if (! $this->isAdminType($user)) {
            abort(403);
        }

        $permit->load([
            'farmLocation',
            'previousFarmLocation',
            'photos',
        ]);

        return view('admin.permits.show', [
            'permit' => $permit,
        ]);
    }

    public function adminChangePassword(Request $request): mixed
    {
        $user = $request->user();

        if (! $this->isAdminType($user)) {
            abort(403);
        }

        return view('auth.change-password-page');
    }
}
