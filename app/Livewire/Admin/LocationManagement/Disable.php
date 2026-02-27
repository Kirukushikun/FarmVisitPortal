<?php

namespace App\Livewire\Admin\LocationManagement;

use App\Models\Location;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Disable extends Component
{
    public bool $showModal = false;

    public int $locationId = 0;

    public string $locationName = '';

    public bool $isCurrentlyDisabled = false;

    public bool $processing = false;

    protected $listeners = ['openDisableLocationModal' => 'openModal'];

    public function openModal($locationId): void
    {
        $locationId = (int) $locationId;
        $cacheKey = CacheKeys::location($locationId);
        $location = Cache::remember($cacheKey, 300, fn () => Location::find($locationId));
        if (! $location) {
            return;
        }

        $this->locationId = (int) $locationId;
        $this->locationName = (string) $location->name;
        $this->isCurrentlyDisabled = (bool) ($location->is_disabled ?? false);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['locationId', 'locationName', 'isCurrentlyDisabled', 'processing']);
    }

    public function toggleDisable(): void
    {
        $this->processing = true;

        try {
            $location = Location::find($this->locationId);
            if (! $location) {
                return;
            }

            $location->update([
                'is_disabled' => ! $this->isCurrentlyDisabled,
            ]);

            Cache::forget(CacheKeys::locationsAll());
            Cache::forget(CacheKeys::location((int) $this->locationId));

            $action = $this->isCurrentlyDisabled ? 'enabled' : 'disabled';
            $locationName = (string) $this->locationName;

            $this->closeModal();
            $this->dispatch('showToast', message: "{$locationName} has been successfully {$action}!", type: 'success');
            $this->dispatch('refreshLocations');
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.location-management.disable-location-management');
    }
}
