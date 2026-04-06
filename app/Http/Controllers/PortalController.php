<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permit;
use App\Models\PermitLog;

class PortalController extends Controller
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function userLocationId(Request $request, $user): int
    {
        $sessionLocationId = (int) $request->session()->get('selected_location_id', 0);
        if ($sessionLocationId > 0) {
            return $sessionLocationId;
        }
        return (int) ($user->farm_location_id ?? 0);
    }

    private function isAdminType($user): bool
    {
        return in_array((int) ($user->user_type ?? 0), [1, 2], true);
    }

    private function isSuperAdminType($user): bool
    {
        return (int) ($user->user_type ?? 0) === 2;
    }

    private function addLog(Permit $permit, int $action, int $userId, ?string $message = null): void
    {
        PermitLog::create([
            'permit_id'  => $permit->id,
            'status'     => $permit->status,
            'action'     => $action,
            'changed_by' => $userId,
            'message'    => $message,
            'red_alert'  => (bool) $permit->red_alert,
        ]);
    }

    // -------------------------------------------------------------------------
    // Page Views
    // -------------------------------------------------------------------------

    public function userHome(Request $request): mixed
    {
        $user = $request->user();
        if ($this->isAdminType($user) && (string) $request->session()->get('ui_mode') !== 'user') {
            return redirect()->route('admin.home');
        }
        return view('user.home');
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
        $userFarmLocationId = $this->userLocationId($request, $user);
        $permitFarmLocationId = (int) ($permit->farm_location_id ?? 0);

        $canView = $isAdmin
            || ((int) ($permit->created_by ?? 0) === (int) ($user->id ?? 0))
            || ((int) ($permit->received_by ?? 0) === (int) ($user->id ?? 0))
            || ($userFarmLocationId > 0 && $permitFarmLocationId > 0 && $userFarmLocationId === $permitFarmLocationId);

        if (! $canView) {
            abort(403);
        }

        $permit->load(['farmLocation', 'photos', 'logs.changedBy']);

        return view('user.permits.show', ['permit' => $permit]);
    }

    public function userScheduledPermits(Request $request): mixed
    {
        return view('user.permits.scheduled', ['user' => $request->user()]);
    }

    public function userMyPermits(Request $request): mixed
    {
        return view('user.permits.my-permits', ['user' => $request->user()]);
    }

    public function userCancelledPermits(Request $request): mixed
    {
        return view('user.permits.cancelled', ['user' => $request->user()]);
    }

    public function adminHome(Request $request): mixed
    {
        $user = $request->user();
        if (! $this->isAdminType($user)) abort(403);
        return view('admin.home');
    }

    public function adminUsers(Request $request): mixed
    {
        $user = $request->user();
        if (! $this->isAdminType($user)) abort(403);
        return view('admin.users');
    }

    public function adminAdmins(Request $request): mixed
    {
        $user = $request->user();
        if (! $this->isSuperAdminType($user)) abort(403);
        return view('admin.admins');
    }

    public function adminLocations(Request $request): mixed
    {
        $user = $request->user();
        if (! $this->isAdminType($user)) abort(403);
        return view('admin.farms');
    }

    public function adminPermits(Request $request): mixed
    {
        $user = $request->user();
        if (! $this->isAdminType($user)) abort(403);
        return view('admin.permits.index');
    }

    public function adminCreatePermit(Request $request): mixed
    {
        $user = $request->user();
        if (! $this->isAdminType($user)) abort(403);
        return view('admin.permits.create');
    }

    public function adminEditPermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();
        if (! $this->isAdminType($user)) abort(403);
        return view('admin.permits.edit');
    }

    public function adminShowPermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();
        if (! $this->isAdminType($user)) abort(403);

        $permit->load(['farmLocation', 'photos', 'logs.changedBy']);

        return view('admin.permits.show', ['permit' => $permit]);
    }

    public function adminChangePassword(Request $request): mixed
    {
        $user = $request->user();
        if (! $this->isAdminType($user)) abort(403);
        return view('auth.change-password-page');
    }

    // -------------------------------------------------------------------------
    // Permit Actions
    // -------------------------------------------------------------------------

    public function acceptPermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();
        $isAdmin = $this->isAdminType($user);
        $userFarmLocationId = $this->userLocationId($request, $user);
        $permitFarmLocationId = (int) ($permit->farm_location_id ?? 0);

        if (! $isAdmin) {
            if ($userFarmLocationId <= 0 || $permitFarmLocationId <= 0 || $userFarmLocationId !== $permitFarmLocationId) {
                abort(403);
            }
        }

        if ((int) ($permit->status ?? 0) !== Permit::STATUS_IN_PROGRESS) {
            return redirect()->back()->with('error', 'Permit cannot be accepted.');
        }

        if ((int) ($permit->received_by ?? 0) !== 0) {
            if ((int) ($permit->received_by ?? 0) === (int) ($user->id ?? 0)) {
                return redirect()->back()->with('success', 'Permit already accepted.');
            }
            return redirect()->back()->with('error', 'Permit has already been accepted by another user.');
        }

        $permit->update(['received_by' => (int) ($user->id ?? 0)]);
        $this->addLog($permit, PermitLog::ACTION_ACCEPTED, (int) $user->id);

        return redirect()->back()->with('success', 'Permit accepted successfully!');
    }

    public function completePermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();
        $isAdmin = $this->isAdminType($user);

        if (! $isAdmin) {
            $permitReceivedBy = (int) ($permit->received_by ?? 0);
            if ($permitReceivedBy !== 0 && $permitReceivedBy !== (int) ($user->id ?? 0)) {
                abort(403);
            }
            $userFarmLocationId = $this->userLocationId($request, $user);
            $permitFarmLocationId = (int) ($permit->farm_location_id ?? 0);
            if ($userFarmLocationId <= 0 || $permitFarmLocationId <= 0 || $userFarmLocationId !== $permitFarmLocationId) {
                abort(403);
            }
        }

        if ((int) ($permit->status ?? 0) !== Permit::STATUS_IN_PROGRESS) {
            return redirect()->back()->with('error', 'Permit cannot be completed.');
        }

        // if ($permit->photos()->count() === 0) {
        //     return redirect()->back()->with('error', 'Please attach at least one visitor ID photo before completing the permit.');
        // }

        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        $remarks = isset($validated['remarks']) && is_string($validated['remarks']) && trim($validated['remarks']) !== ''
            ? trim($validated['remarks'])
            : null;

        $permit->update([
            'status'       => Permit::STATUS_COMPLETED,
            'completed_at' => now(),
            'received_by'  => $user->id,
            'remarks'      => $remarks,
        ]);

        $this->addLog($permit, PermitLog::ACTION_COMPLETED, (int) $user->id, $remarks);

        return redirect()->route('user.home')->with('success', 'Permit marked as completed successfully!');
    }

    public function cancelPermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();
        $isAdmin = $this->isAdminType($user);

        if (! $isAdmin) {
            $permitReceivedBy = (int) ($permit->received_by ?? 0);
            if ($permitReceivedBy !== 0 && $permitReceivedBy !== (int) ($user->id ?? 0)) {
                abort(403);
            }
            $userFarmLocationId = $this->userLocationId($request, $user);
            $permitFarmLocationId = (int) ($permit->farm_location_id ?? 0);
            if ($userFarmLocationId <= 0 || $permitFarmLocationId <= 0 || $userFarmLocationId !== $permitFarmLocationId) {
                abort(403);
            }
        }

        if (! in_array((int) ($permit->status ?? 0), [Permit::STATUS_IN_PROGRESS, Permit::STATUS_ON_HOLD])) {
            return redirect()->back()->with('error', 'Permit cannot be cancelled.');
        }

        $permit->update([
            'status'      => Permit::STATUS_CANCELLED,
            'received_by' => $user->id,
            'remarks'     => null,
        ]);

        $this->addLog($permit, PermitLog::ACTION_CANCELLED, (int) $user->id);

        return redirect()->route('user.home')->with('success', 'Permit cancelled successfully!');
    }

    public function holdPermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();
        $userFarmLocationId = $this->userLocationId($request, $user);
        $permitFarmLocationId = (int) ($permit->farm_location_id ?? 0);

        if ($userFarmLocationId <= 0 || $permitFarmLocationId <= 0 || $userFarmLocationId !== $permitFarmLocationId) {
            abort(403);
        }

        if ((int) ($permit->status ?? 0) !== Permit::STATUS_IN_PROGRESS) {
            return redirect()->back()->with('error', 'Permit cannot be put on hold.');
        }

        $validated = $request->validate([
            'hold_reason' => ['required', 'string', 'max:5000'],
            'red_alert'   => ['nullable', 'boolean'],
        ]);

        $redAlert = (bool) ($validated['red_alert'] ?? false);

        $permit->update([
            'status'    => Permit::STATUS_ON_HOLD,
            'red_alert' => $redAlert,
        ]);

        $this->addLog($permit, PermitLog::ACTION_HELD, (int) $user->id, trim($validated['hold_reason']));

        return redirect()->back()->with('success', 'Permit placed on hold.');
    }

    public function respondToHold(Request $request, Permit $permit): mixed
    {
        $user = $request->user();

        if (! $this->isAdminType($user)) {
            abort(403);
        }

        if ((int) ($permit->status ?? 0) !== Permit::STATUS_ON_HOLD) {
            return redirect()->back()->with('error', 'Permit is not on hold.');
        }

        $validated = $request->validate([
            'action'         => ['required', 'in:approve,reject'], // removed 'return'
            'admin_response' => ['nullable', 'string', 'max:5000'],
        ]);

        $message = isset($validated['admin_response']) && trim($validated['admin_response']) !== ''
            ? trim($validated['admin_response'])
            : null;

        $newStatus = match ($validated['action']) {
            'approve' => Permit::STATUS_IN_PROGRESS,
            'reject'  => Permit::STATUS_CANCELLED,
        };

        $actionCode = match ($validated['action']) {
            'approve' => PermitLog::ACTION_APPROVED,
            'reject'  => PermitLog::ACTION_REJECTED,
        };

        $permit->update([
            'status'       => $newStatus,
            'red_alert'    => $newStatus === Permit::STATUS_IN_PROGRESS ? false : $permit->red_alert,
            'completed_at' => $newStatus === Permit::STATUS_CANCELLED ? now() : $permit->completed_at,
        ]);

        $this->addLog($permit, $actionCode, (int) $user->id, $message);

        return redirect()->back()->with('success', 'Response submitted successfully.');
    }

    /**
     * Admin overrides a rejection back to In Progress.
     * Only available when status === Cancelled AND last log action === Rejected.
     */
    public function overrideReject(Request $request, Permit $permit): mixed
    {
        $user = $request->user();

        if (! $this->isAdminType($user)) {
            abort(403);
        }

        // Can only override a permit that was cancelled via a rejection
        $lastAdminLog = $permit->lastAdminLog();
        if (
            (int) ($permit->status ?? 0) !== Permit::STATUS_CANCELLED
            || ! $lastAdminLog
            || (int) $lastAdminLog->action !== PermitLog::ACTION_REJECTED
        ) {
            return redirect()->back()->with('error', 'This permit cannot be overridden.');
        }

        $validated = $request->validate([
            'admin_response' => ['nullable', 'string', 'max:5000'],
        ]);

        $message = isset($validated['admin_response']) && trim($validated['admin_response']) !== ''
            ? trim($validated['admin_response'])
            : null;

        $permit->update([
            'status'       => Permit::STATUS_IN_PROGRESS,
            'red_alert'    => false,
            'completed_at' => null,
        ]);
        
        $this->addLog($permit, PermitLog::ACTION_OVERRIDE, (int) $user->id, $message);

        return redirect()->back()->with('success', 'Permit overridden. Visitors may now enter.');
    }

    public function resubmitPermit(Request $request, Permit $permit): mixed
    {
        $user = $request->user();

        if ((int) ($permit->status ?? 0) !== Permit::STATUS_RETURNED) {
            return redirect()->back()->with('error', 'Permit cannot be resubmitted.');
        }

        $isAdmin = $this->isAdminType($user);
        if (! $isAdmin && (int) ($permit->created_by ?? 0) !== (int) ($user->id ?? 0)) {
            abort(403);
        }

        $permit->update(['status' => Permit::STATUS_SCHEDULED]);

        $this->addLog($permit, PermitLog::ACTION_RESUBMITTED, (int) $user->id);

        return redirect()->route('admin.permits.edit', $permit)
            ->with('success', 'Permit returned to scheduled. Please update and resubmit.');
    }
}