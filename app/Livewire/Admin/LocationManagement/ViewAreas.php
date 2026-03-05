<?php

namespace App\Livewire\Admin\LocationManagement;

use App\Models\Area;
use App\Models\Location;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ViewAreas extends Component
{
    public int $locationId = 0;

    public string $locationName = '';

    public bool $showModal = false;

    public string $search = '';

    public string $statusFilter = 'all'; // all, enabled, disabled

    public bool $showStatusFilterDropdown = false;

    public bool $showAddAreaModal = false;

    public bool $showDisableConfirmModal = false;

    public bool $showDeleteConfirmModal = false;

    public string $confirmAreaName = '';

    public int $confirmAreaId = 0;

    public bool $confirmAreaIsDisabled = false;

    public bool $processing = false;

    public int $page = 1;

    public int $perPage = 10;

    public string $newAreaName = '';

    public int $editingAreaId = 0;

    public string $editingAreaName = '';

    protected array $queryString = [
        'page' => ['except' => 1],
        'perPage' => ['except' => 10],
    ];

    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    public function updatedStatusFilter(): void
    {
        $this->page = 1;
    }

    protected $listeners = ['openViewAreasLocationModal' => 'openModal'];

    public function openModal($locationId): void
    {
        $locationId = (int) $locationId;

        $location = Cache::remember(CacheKeys::location($locationId), 300, fn () => Location::find($locationId));
        if (! $location) {
            return;
        }

        $this->locationId = $locationId;
        $this->locationName = (string) $location->name;

        $this->reset(['search', 'statusFilter', 'showStatusFilterDropdown', 'showAddAreaModal', 'showDisableConfirmModal', 'showDeleteConfirmModal', 'confirmAreaName', 'confirmAreaId', 'confirmAreaIsDisabled', 'processing', 'page', 'perPage', 'newAreaName', 'editingAreaId', 'editingAreaName']);
        $this->resetValidation();

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['locationId', 'locationName', 'search', 'statusFilter', 'showStatusFilterDropdown', 'showAddAreaModal', 'showDisableConfirmModal', 'showDeleteConfirmModal', 'confirmAreaName', 'confirmAreaId', 'confirmAreaIsDisabled', 'processing', 'page', 'perPage', 'newAreaName', 'editingAreaId', 'editingAreaName']);
        $this->resetValidation();
    }

    public function openAddAreaModal(): void
    {
        $this->showAddAreaModal = true;
        $this->reset(['newAreaName']);
        $this->resetValidation();
    }

    public function closeAddAreaModal(): void
    {
        $this->showAddAreaModal = false;
        $this->reset(['newAreaName']);
        $this->resetValidation();
    }

    public function openDisableConfirmModal($areaId): void
    {
        $areaId = (int) $areaId;
        $area = Area::query()
            ->where('location_id', $this->locationId)
            ->find($areaId);

        if (! $area) {
            return;
        }

        $this->confirmAreaId = $areaId;
        $this->confirmAreaName = (string) $area->name;
        $this->confirmAreaIsDisabled = (bool) $area->is_disabled;
        $this->showDisableConfirmModal = true;
    }

    public function closeDisableConfirmModal(): void
    {
        $this->showDisableConfirmModal = false;
        $this->reset(['confirmAreaId', 'confirmAreaName', 'confirmAreaIsDisabled']);
    }

    public function confirmDisable(): void
    {
        $this->processing = true;

        try {
            $areaId = $this->confirmAreaId;
            if ($areaId <= 0) {
                return;
            }

            $area = Area::query()
                ->where('location_id', $this->locationId)
                ->find($areaId);

            if (! $area) {
                return;
            }

            $area->update([
                'is_disabled' => ! (bool) $area->is_disabled,
            ]);

            $action = $this->confirmAreaIsDisabled ? 'enabled' : 'disabled';
            $areaName = (string) $this->confirmAreaName;

            $this->closeDisableConfirmModal();
            $this->dispatch('showToast', message: "{$areaName} has been successfully {$action}!", type: 'success');
            $this->dispatch('refreshLocations');
        } finally {
            $this->processing = false;
        }
    }

    public function openDeleteConfirmModal($areaId): void
    {
        $areaId = (int) $areaId;
        $area = Area::query()
            ->where('location_id', $this->locationId)
            ->find($areaId);

        if (! $area) {
            return;
        }

        $this->confirmAreaId = $areaId;
        $this->confirmAreaName = (string) $area->name;
        $this->showDeleteConfirmModal = true;
    }

    public function closeDeleteConfirmModal(): void
    {
        $this->showDeleteConfirmModal = false;
        $this->reset(['confirmAreaId', 'confirmAreaName', 'confirmAreaIsDisabled']);
    }

    public function confirmDelete(): void
    {
        $areaId = $this->confirmAreaId;
        if ($areaId <= 0) {
            return;
        }

        $area = Area::query()
            ->where('location_id', $this->locationId)
            ->find($areaId);

        if (! $area) {
            return;
        }

        $areaName = (string) $area->name;
        $area->delete();

        if ($this->editingAreaId === $areaId) {
            $this->cancelEdit();
        }

        $this->closeDeleteConfirmModal();
        $this->dispatch('showToast', message: "{$areaName} has been successfully deleted!", type: 'success');
        $this->dispatch('refreshLocations');
    }

    public function toggleStatusFilterDropdown(): void
    {
        $this->showStatusFilterDropdown = ! $this->showStatusFilterDropdown;
    }

    public function setStatusFilter(string $value): void
    {
        if (! in_array($value, ['all', 'enabled', 'disabled'], true)) {
            return;
        }

        $this->statusFilter = $value;
        $this->showStatusFilterDropdown = false;
    }

    public function createArea(): void
    {
        $name = trim($this->newAreaName);
        $this->newAreaName = $name;

        $this->validate([
            'newAreaName' => [
                'required',
                'string',
                'min:1',
                'max:100',
                Rule::unique('areas', 'name')->where(fn ($query) => $query->where('location_id', $this->locationId)),
            ],
        ]);

        Area::create([
            'location_id' => $this->locationId,
            'name' => $name,
            'is_disabled' => false,
        ]);

        $this->closeAddAreaModal();
        $this->dispatch('showToast', message: "{$name} has been successfully created!", type: 'success');
        $this->dispatch('refreshLocations');
    }

    public function startEdit($areaId): void
    {
        $areaId = (int) $areaId;
        $area = Area::query()
            ->where('location_id', $this->locationId)
            ->find($areaId);

        if (! $area) {
            return;
        }

        $this->editingAreaId = (int) $area->id;
        $this->editingAreaName = (string) $area->name;
        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingAreaId', 'editingAreaName']);
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        if ($this->editingAreaId <= 0) {
            return;
        }

        $name = trim($this->editingAreaName);
        $this->editingAreaName = $name;

        $this->validate([
            'editingAreaName' => [
                'required',
                'string',
                'min:1',
                'max:100',
                Rule::unique('areas', 'name')
                    ->where(fn ($query) => $query->where('location_id', $this->locationId))
                    ->ignore($this->editingAreaId),
            ],
        ]);

        $area = Area::query()
            ->where('location_id', $this->locationId)
            ->find($this->editingAreaId);

        if (! $area) {
            return;
        }

        $area->update(['name' => $name]);

        $this->cancelEdit();
        $this->dispatch('showToast', message: "{$name} has been successfully updated!", type: 'success');
        $this->dispatch('refreshLocations');
    }

    public function toggleDisable($areaId): void
    {
        $this->openDisableConfirmModal($areaId);
    }

    public function deleteArea($areaId): void
    {
        $this->openDeleteConfirmModal($areaId);
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function previousPage(): void
    {
        $this->page--;
    }

    public function gotoPage($page): void
    {
        $this->page = (int) $page;
    }

    public function render()
    {
        $areas = Area::query()
            ->where('location_id', $this->locationId)
            ->when($this->statusFilter === 'disabled', function ($query) {
                $query->where('is_disabled', true);
            })
            ->when($this->statusFilter === 'enabled', function ($query) {
                $query->where('is_disabled', false);
            })
            ->when($this->search !== '', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate($this->perPage, ['*'], 'page', $this->page);

        return view('livewire.admin.location-management.view-areas-location-management', [
            'areas' => $areas,
        ]);
    }
}
