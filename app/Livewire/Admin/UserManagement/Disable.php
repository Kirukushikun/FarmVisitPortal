<?php

namespace App\Livewire\Admin\UserManagement;

use App\Models\User;
use Livewire\Component;

class Disable extends Component
{
    public bool $showModal = false;

    public int $userId = 0;

    public string $userName = '';

    public bool $isCurrentlyDisabled = false;

    public bool $processing = false;

    protected $listeners = ['openDisableModal' => 'openModal'];

    public function openModal($userId): void
    {
        $user = User::where('user_type', 0)->find((int) $userId);
        if (! $user) {
            return;
        }

        $this->userId = (int) $userId;
        $this->userName = trim((string) $user->first_name . ' ' . (string) $user->last_name);
        $this->isCurrentlyDisabled = (bool) ($user->is_disabled ?? false);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['userId', 'userName', 'isCurrentlyDisabled', 'processing']);
    }

    public function toggleDisable(): void
    {
        $this->processing = true;

        try {
            $user = User::where('user_type', 0)->find($this->userId);
            if (! $user) {
                return;
            }

            $user->update([
                'is_disabled' => ! $this->isCurrentlyDisabled,
            ]);

            $action = $this->isCurrentlyDisabled ? 'enabled' : 'disabled';
            $userName = (string) $this->userName;
            $this->closeModal();
            $this->dispatch('showToast', message: "{$userName} has been successfully {$action}!", type: 'success');
            $this->dispatch('refreshUsers');
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.user-management.disable-user-management');
    }
}
