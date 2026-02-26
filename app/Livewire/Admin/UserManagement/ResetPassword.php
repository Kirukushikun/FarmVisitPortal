<?php

namespace App\Livewire\Admin\UserManagement;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ResetPassword extends Component
{
    public int $userId = 0;

    public string $userName = '';

    public bool $showModal = false;

    protected $listeners = ['openResetPasswordModal' => 'openModal'];

    public function openModal($userId): void
    {
        $user = User::where('user_type', 0)->find((int) $userId);
        if ($user) {
            $this->userId = (int) $userId;
            $this->userName = trim((string) $user->first_name . ' ' . (string) $user->last_name);
            $this->showModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['userId', 'userName']);
    }

    public function resetPassword(): void
    {
        $user = User::where('user_type', 0)->find($this->userId);
        if (! $user) {
            return;
        }

        $userName = trim((string) $user->first_name . ' ' . (string) $user->last_name);

        $user->update([
            'password' => Hash::make('brookside25'),
        ]);

        $this->closeModal();
        $this->dispatch('showToast', message: "Password for {$userName} has been reset successfully!", type: 'success');
        $this->dispatch('refreshUsers');
    }

    public function render()
    {
        return view('livewire.admin.user-management.reset-password-user-management');
    }
}
