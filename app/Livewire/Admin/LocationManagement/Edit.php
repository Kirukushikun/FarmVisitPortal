<?php

namespace App\Livewire\Admin\LocationManagement;

use App\Models\Location;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Edit extends Component
{
    public int $locationId = 0;

    #[Validate('required|string|min:2|max:100')]
    public string $name = '';

    #[Validate('required|integer|in:0,1')]
    public int $farmType = 0;

    public bool $showModal = false;

    protected $listeners = ['openEditLocationModal' => 'openModal'];

    public function openModal($locationId): void
    {
        $locationId = (int) $locationId;
        $cacheKey = CacheKeys::location($locationId);
        $location = Cache::remember($cacheKey, 300, fn () => Location::find($locationId));
        if (! $location) {
            return;
        }

        $this->locationId = $locationId;
        $this->name = (string) $location->name;
        $this->farmType = (int) ($location->farm_type ?? 0);
        if ($this->farmType !== 0 && $this->farmType !== 1) {
            $this->farmType = 0;
        }
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['locationId', 'name', 'farmType']);
        $this->resetValidation();
    }

    public function updateLocation(): void
    {
        $this->validate();

        $location = Location::find($this->locationId);
        if (! $location) {
            return;
        }

        $this->validate([
            'name' => 'required|string|min:2|max:100|unique:locations,name,' . $this->locationId,
        ]);

        $location->update([
            'name' => $this->name,
            'farm_type' => (int) $this->farmType,
        ]);

        Cache::forget(CacheKeys::locationsAll());
        Cache::forget(CacheKeys::location((int) $this->locationId));

        $locationName = trim($this->name);
        $this->closeModal();
        $this->dispatch('showToast', message: "{$locationName} has been updated successfully!", type: 'success');
        $this->dispatch('refreshLocations');
    }

    public function render()
    {
        return view('livewire.admin.location-management.edit-location-management');
    }
}
