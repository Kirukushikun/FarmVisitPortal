<?php

namespace App\Livewire\Admin\LocationManagement;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Display extends Component
{
    public string $search = '';

    public int $perPage = 10;

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public int $page = 1;

    public string $statusFilter = 'all'; // all, enabled, disabled

    public bool $showFilterDropdown = false;

    protected array $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'statusFilter' => ['except' => 'all'],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    protected $listeners = ['refreshLocations' => '$refresh'];

    public function mount(): void
    {
        $this->search = request('search', '');
        $this->page = request('page', 1);
        $this->statusFilter = request('statusFilter', 'all');
    }

    public function updatingSearch(): void
    {
        $this->page = 1;
    }

    public function updatingStatusFilter(): void
    {
        $this->page = 1;
    }

    public function updatingPerPage(): void
    {
        $this->page = 1;
    }

    public function toggleFilterDropdown(): void
    {
        $this->showFilterDropdown = ! $this->showFilterDropdown;
    }

    public function resetFilters(): void
    {
        $this->statusFilter = 'all';
        $this->page = 1;
        $this->showFilterDropdown = false;
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->page = 1;
    }

    public function gotoPage(int $page): void
    {
        $page = (int) $page;
        if ($page < 1) {
            $page = 1;
        }

        $totalPages = $this->baseQuery()->paginate($this->perPage)->lastPage();
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->page = $page;
    }

    protected function baseQuery(): Builder
    {
        $query = Location::query()
            ->where('locations.name', 'like', '%' . $this->search . '%');

        // Use a join instead of subquery to maintain Eloquent Builder
        $query->leftJoin('areas', function ($join) {
            $join->on('areas.location_id', '=', 'locations.id')
                 ->where('areas.is_disabled', false);
        })
        ->selectRaw('locations.*, COUNT(areas.id) as areas_count')
        ->groupBy('locations.id');

        if ($this->statusFilter === 'disabled') {
            $query->where('locations.is_disabled', true);
        } elseif ($this->statusFilter === 'enabled') {
            $query->where('locations.is_disabled', false);
        }

        return $query;
    }

    public function getPaginationData(): array
    {
        $locations = $this->baseQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage, ['*'], 'page', $this->page);

        $currentPage = $locations->currentPage();
        $lastPage = $locations->lastPage();
        $this->page = $currentPage;

        if ($lastPage <= 3) {
            $startPage = 1;
            $endPage = $lastPage;
        } elseif ($currentPage === 1) {
            $startPage = 1;
            $endPage = min(3, $lastPage);
        } elseif ($currentPage === $lastPage) {
            $startPage = max(1, $lastPage - 2);
            $endPage = $lastPage;
        } else {
            $startPage = max(1, $currentPage - 1);
            $endPage = min($lastPage, $currentPage + 1);
        }

        $pages = [];
        for ($i = $startPage; $i <= $endPage; $i++) {
            $pages[] = $i;
        }

        return [
            'locations' => $locations,
            'pages' => $pages,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
        ];
    }

    public function render()
    {
        return view('livewire.admin.location-management.display-location-management', $this->getPaginationData());
    }
}
