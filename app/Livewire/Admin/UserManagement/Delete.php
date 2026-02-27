<?php

namespace App\Livewire\Admin\UserManagement;

use App\Models\User;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Delete extends Component
{
    public int $userId = 0;

    public string $userName = '';

    public bool $showModal = false;

    protected $listeners = ['openDeleteModal' => 'openModal'];

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

    public function deleteUser(): void
    {
        $user = User::where('user_type', 0)->find($this->userId);
        if (! $user) {
            return;
        }

        $userName = trim((string) $user->first_name . ' ' . (string) $user->last_name);

        $user->delete();

        Cache::forget(CacheKeys::usersAll());
        Cache::forget(CacheKeys::user((int) $this->userId));

        $this->closeModal();
        $this->dispatch('showToast', message: "{$userName} has been successfully deleted!", type: 'success');
        $this->dispatch('refreshUsers');
    }

    public function render()
    {
        return view('livewire.admin.user-management.delete-user-management');
    }
}
