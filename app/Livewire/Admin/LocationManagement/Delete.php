<?php

namespace App\Livewire\Admin\LocationManagement;

use App\Models\Location;
use Livewire\Component;

class Delete extends Component
{
    public int $locationId = 0;

    public string $locationName = '';

    public bool $showModal = false;

    protected $listeners = ['openDeleteLocationModal' => 'openModal'];

    public function openModal($locationId): void
    {
        $location = Location::find((int) $locationId);
        if (! $location) {
            return;
        }

        $this->locationId = (int) $locationId;
        $this->locationName = (string) $location->name;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['locationId', 'locationName']);
    }

    public function deleteLocation(): void
    {
        $location = Location::find($this->locationId);
        if (! $location) {
            return;
        }

        $locationName = (string) $location->name;
        $location->delete();

        $this->closeModal();
        $this->dispatch('showToast', message: "{$locationName} has been successfully deleted!", type: 'success');
        $this->dispatch('refreshLocations');
    }

    public function render()
    {
        return view('livewire.admin.location-management.delete-location-management');
    }
}
