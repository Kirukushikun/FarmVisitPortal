<?php

namespace App\Livewire\Admin\UserManagement;

use App\Models\User;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Create extends Component
{
    #[Validate('required|string|min:2|max:50')]
    public string $firstName = '';

    #[Validate('required|string|min:2|max:50')]
    public string $lastName = '';

    public bool $showModal = false;

    protected $listeners = ['openCreateModal' => 'openModal'];

    public function openModal(): void
    {
        $this->reset(['firstName', 'lastName']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['firstName', 'lastName']);
        $this->resetValidation();
    }

    public function createUser(): void
    {
        $this->validate();

        $baseUsername = strtoupper(substr($this->firstName, 0, 1)) . preg_replace('/\s+/', '', $this->lastName);
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        $user = User::create([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'user_type' => 0,
            'is_disabled' => false,
            'username' => $username,
            'password' => Hash::make('brookside25'),
        ]);

        unset($user);

        Cache::forget(CacheKeys::usersAll());

        $this->closeModal();
        $fullName = trim($this->firstName . ' ' . $this->lastName);
        $this->dispatch('showToast', message: "{$fullName} has been created successfully!", type: 'success');
        $this->dispatch('refreshUsers');
    }

    public function render()
    {
        return view('livewire.admin.user-management.create-user-management');
    }
}
