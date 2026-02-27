<?php

namespace App\Livewire\Admin\UserManagement;

use App\Models\User;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
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
        $userId = (int) $userId;
        $cacheKey = CacheKeys::user($userId);
        $user = Cache::remember($cacheKey, 300, fn () => User::where('user_type', 0)->find($userId));
        if ($user) {
            $this->userId = $userId;
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

        Cache::forget(CacheKeys::usersAll());
        Cache::forget(CacheKeys::user((int) $this->userId));

        $this->closeModal();
        $this->dispatch('showToast', message: "Password for {$userName} has been reset successfully!", type: 'success');
        $this->dispatch('refreshUsers');
    }

    public function render()
    {
        return view('livewire.admin.user-management.reset-password-user-management');
    }
}
