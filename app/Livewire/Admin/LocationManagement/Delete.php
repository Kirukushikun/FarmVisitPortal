<?php

namespace App\Livewire\Admin\LocationManagement;

use App\Models\Location;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;


class Delete extends Component
{
    public int $locationId = 0;

    public string $locationName = '';

    public bool $showModal = false;

    protected $listeners = ['openDeleteLocationModal' => 'openModal'];

    public function openModal($locationId): void
    {
        $locationId = (int) $locationId;
        $cacheKey = CacheKeys::location($locationId);
        $location = Cache::remember($cacheKey, 300, fn () => Location::find($locationId));
        if (! $location) {
            return;
        }

        $this->locationId = $locationId;
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

        try {
            $location->delete();

            Cache::forget(CacheKeys::locationsAll());
            Cache::forget(CacheKeys::location((int) $this->locationId));

            $this->closeModal();
            $this->dispatch('showToast', message: "{$locationName} has been successfully deleted!", type: 'success');
            $this->dispatch('refreshLocations');

        } catch (QueryException $e) {
            $this->dispatch('showToast', message: "Cannot delete {$locationName} because it is being used in existing permits.", type: 'error');
            $this->closeModal();
        }
    }

    public function render()
    {
        return view('livewire.admin.location-management.delete-location-management');
    }
}
