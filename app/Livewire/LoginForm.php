<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LoginForm extends Component
{
    #[Validate('required|in:user,admin')]
    public string $role = 'user';

    #[Validate('required|string')]
    public string $username = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $showPassword = false;

    public function submit()
    {
        $this->validate();

        if (! Auth::attempt(['username' => $this->username, 'password' => $this->password])) {
            $this->addError('username', 'Invalid credentials.');
            return;
        }

        session()->regenerate();

        $user = Auth::user();
        $isAdmin = (int) ($user->user_type ?? 0) === 1;
        $expectedAdmin = $this->role === 'admin';

        if ($expectedAdmin !== $isAdmin) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            $this->addError('role', $expectedAdmin
                ? 'This account is not an admin.'
                : 'This account is not a user.'
            );
            return;
        }

        return redirect()->route($expectedAdmin ? 'admin.home' : 'user.home');
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
