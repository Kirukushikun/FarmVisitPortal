<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ChangePassword extends Component
{
    public $currentPassword = '';
    public $newPassword = '';
    public $newPassword_confirmation = '';

    protected function rules()
    {
        return [
            'currentPassword' => ['required'],
            'newPassword' => ['required', 'min:8', 'different:currentPassword'],
            'newPassword_confirmation' => ['required'],
        ];
    }

    protected $messages = [
        'currentPassword.required' => 'Current password is required',
        'newPassword.required' => 'New password is required',
        'newPassword.min' => 'Password must be at least 8 characters long',
        'newPassword.different' => 'New password must be different from current password',
        'newPassword_confirmation.required' => 'Password confirmation is required',
    ];

    public function changePassword()
    {
        $this->validate();

        $user = Auth::user();

        if (! Hash::check($this->currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'currentPassword' => 'The current password is incorrect.',
            ]);
        }

        if ($this->newPassword !== $this->newPassword_confirmation) {
            $this->addError('newPassword_confirmation', 'The password confirmation does not match.');
            return;
        }

        $user->password = Hash::make($this->newPassword);
        $user->save();

        $this->reset(['currentPassword', 'newPassword', 'newPassword_confirmation']);

        session()->flash('status', 'Your password has been changed successfully!');
    }

    public function render()
    {
        return view('livewire.auth.change-password');
    }
}
