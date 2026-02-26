<?php

namespace App\Livewire\Admin\UserManagement;

use App\Models\User;
use Livewire\Component;

class Delete extends Component
{
    public int $userId = 0;

    public string $userName = '';

    public bool $showModal = false;

    protected $listeners = ['openDeleteModal' => 'openModal'];

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

    public function deleteUser(): void
    {
        $user = User::where('user_type', 0)->find($this->userId);
        if (! $user) {
            return;
        }

        $userName = trim((string) $user->first_name . ' ' . (string) $user->last_name);

        $user->delete();

        $this->closeModal();
        $this->dispatch('showToast', message: "{$userName} has been successfully deleted!", type: 'success');
        $this->dispatch('refreshUsers');
    }

    public function render()
    {
        return view('livewire.admin.user-management.delete-user-management');
    }
}
