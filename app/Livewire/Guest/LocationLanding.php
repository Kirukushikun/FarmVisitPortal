<?php

namespace App\Livewire\Guest;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LocationLanding extends Component
{
    public string $search = '';

    public int $perPage = 12;

    public int $page = 1;

    protected array $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->search = (string) request()->query('search', '');
        $this->page = (int) request()->query('page', 1);

        if ($this->page < 1) {
            $this->page = 1;
        }
    }

    public function updatingSearch(): void
    {
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
        $query = Location::query();
        $query->addSelect('locations.*');

        $query->getQuery()->selectSub(
            DB::table('permits')
                ->selectRaw('count(*)')
                ->whereColumn('destination_location_id', 'locations.id'),
            'destination_permits_count'
        );

        return $query
            ->where('is_disabled', false)
            ->where('name', 'like', '%' . $this->search . '%');
    }

    public function getPaginationData(): array
    {
        $locations = $this->baseQuery()
            ->orderBy('name')
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
        return view('livewire.guest.location-landing', $this->getPaginationData());
    }
}
