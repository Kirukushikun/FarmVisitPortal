<?php

namespace App\Livewire\Admin\UserManagement;

use App\Models\User;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Edit extends Component
{
    public int $userId = 0;

    #[Validate('required|string|min:2|max:50')]
    public string $firstName = '';

    #[Validate('required|string|min:2|max:50')]
    public string $lastName = '';

    public bool $showModal = false;

    protected $listeners = ['openEditModal' => 'openModal'];

    public function openModal($userId): void
    {
        $userId = (int) $userId;
        $cacheKey = CacheKeys::user($userId);
        $user = Cache::remember($cacheKey, 300, fn () => User::where('user_type', 0)->find($userId));
        if (! $user) {
            return;
        }

        $this->userId = $userId;
        $this->firstName = (string) $user->first_name;
        $this->lastName = (string) $user->last_name;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['userId', 'firstName', 'lastName']);
        $this->resetValidation();
    }

    public function updateUser(): void
    {
        $this->validate();

        $user = User::where('user_type', 0)->find($this->userId);
        if (! $user) {
            return;
        }

        $baseUsername = strtoupper(substr($this->firstName, 0, 1)) . preg_replace('/\s+/', '', $this->lastName);
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->where('id', '!=', $this->userId)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        $user->update([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'username' => $username,
        ]);

        Cache::forget(CacheKeys::usersAll());
        Cache::forget(CacheKeys::user((int) $this->userId));

        $fullName = trim($this->firstName . ' ' . $this->lastName);
        $this->closeModal();
        $this->dispatch('showToast', message: "{$fullName} has been updated successfully!", type: 'success');
        $this->dispatch('refreshUsers');
    }

    public function render()
    {
        return view('livewire.admin.user-management.edit-user-management');
    }
}
