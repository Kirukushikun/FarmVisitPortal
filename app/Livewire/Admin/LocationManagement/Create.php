<?php

namespace App\Livewire\Admin\LocationManagement;

use App\Models\Location;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Create extends Component
{
    #[Validate('required|string|min:2|max:100|unique:locations,name')]
    public string $name = '';

    public bool $showModal = false;

    protected $listeners = ['openCreateLocationModal' => 'openModal'];

    public function openModal(): void
    {
        $this->reset(['name']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['name']);
        $this->resetValidation();
    }

    public function createLocation(): void
    {
        $this->validate();

        Location::create([
            'name' => $this->name,
            'is_disabled' => false,
        ]);

        $locationName = trim($this->name);
        $this->closeModal();
        $this->dispatch('showToast', message: "{$locationName} has been created successfully!", type: 'success');
        $this->dispatch('refreshLocations');
    }

    public function render()
    {
        return view('livewire.admin.location-management.create-location-management');
    }
}
