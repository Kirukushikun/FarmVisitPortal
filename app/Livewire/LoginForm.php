<?php

namespace App\Livewire;

use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LoginForm extends Component
{
    public ?string $forcedRole = null;

    public ?string $selectedLocationName = null;

    #[Validate('required|in:user,admin')]
    public string $role = 'user';

    #[Validate('required|string')]
    public string $username = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $showPassword = false;

    public function mount(?string $forcedRole = null, ?string $selectedLocationName = null): void
    {
        $this->forcedRole = $forcedRole;
        $this->selectedLocationName = $selectedLocationName;

        if ($this->forcedRole === 'admin') {
            $this->role = 'admin';
            session()->forget('selected_location_id');
        } elseif ($this->forcedRole === 'user') {
            $this->role = 'user';
        }
    }

    public function submit()
    {
        $this->validate();

        if ($this->role === 'user') {
            $locationId = (int) session()->get('selected_location_id', 0);
            $hasValidLocation = $locationId > 0
                && Location::whereKey($locationId)->where('is_disabled', false)->exists();

            if (! $hasValidLocation) {
                $this->addError('role', 'Please select a location first.');
                return;
            }
        }

        if (! Auth::attempt(['username' => $this->username, 'password' => $this->password])) {
            $this->addError('username', 'Invalid credentials.');
            return;
        }

        session()->regenerate();

        $user = Auth::user();
        if (($user->is_disabled ?? false) === true) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            $this->addError('username', 'This account is disabled.');
            return;
        }

        $isAdmin = (int) ($user->user_type ?? 0) === 1;
        $expectedAdmin = $this->role === 'admin';

        if ($expectedAdmin && ! $isAdmin) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            $this->addError('role', 'This account is not an admin.');
            return;
        }

        if ($expectedAdmin) {
            session()->forget('selected_location_id');
            session()->put('ui_mode', 'admin');

            return redirect()->route('admin.home');
        }

        session()->put('ui_mode', 'user');

        return redirect()->route('user.home');
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
