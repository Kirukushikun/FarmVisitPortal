<?php

namespace App\Livewire\Admin\UserManagement;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Display extends Component
{
    public string $search = '';

    public int $perPage = 10;

    public string $sortField = 'first_name';

    public string $sortDirection = 'asc';

    public int $page = 1;

    public string $statusFilter = 'all'; // all, enabled, disabled

    public string $dateFrom = '';

    public string $dateTo = '';

    public bool $showFilterDropdown = false;

    protected array $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'statusFilter' => ['except' => 'all'],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'first_name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    protected $listeners = ['refreshUsers' => '$refresh'];

    public function mount(): void
    {
        $this->search = (string) request()->query('search', '');
        $this->page = (int) request()->query('page', 1);
        $this->statusFilter = (string) request()->query('statusFilter', 'all');
        $this->dateFrom = (string) request()->query('dateFrom', '');
        $this->dateTo = (string) request()->query('dateTo', '');
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

    public function updatedDateFrom(): void
    {
        if ($this->dateFrom && $this->dateTo && $this->dateFrom > $this->dateTo) {
            $this->dateTo = '';
        }
        $this->page = 1;
    }

    public function updatedDateTo(): void
    {
        if ($this->dateTo && $this->dateFrom && $this->dateTo < $this->dateFrom) {
            $this->dateTo = '';
        }
        $this->page = 1;
    }

    public function toggleFilterDropdown(): void
    {
        $this->showFilterDropdown = ! $this->showFilterDropdown;
    }

    public function resetFilters(): void
    {
        $this->statusFilter = 'all';
        $this->dateFrom = '';
        $this->dateTo = '';
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
        $users = User::query()
            ->where('user_type', 0)
            ->where(function (Builder $query) {
                $query
                    ->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%');
            });

        if ($this->statusFilter === 'disabled') {
            $users->where('is_disabled', true);
        } elseif ($this->statusFilter === 'enabled') {
            $users->where('is_disabled', false);
        }

        if ($this->dateFrom || $this->dateTo) {
            if ($this->dateFrom && $this->dateTo) {
                $users->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
            } elseif ($this->dateFrom) {
                $users->whereDate('created_at', '>=', $this->dateFrom);
            } elseif ($this->dateTo) {
                $users->whereDate('created_at', '<=', $this->dateTo);
            }
        }

        return $users;
    }

    public function getPaginationData(): array
    {
        $users = $this->baseQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage, ['*'], 'page', $this->page);

        $currentPage = $users->currentPage();
        $lastPage = $users->lastPage();
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
            'users' => $users,
            'pages' => $pages,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
        ];
    }

    public function render()
    {
        return view('livewire.admin.user-management.display-user-management', $this->getPaginationData());
    }
}
