<?php

namespace App\Livewire\Admin\Permits;

use App\Models\Permit;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Dashboard extends Component
{
    public string $search = '';

    public int $perPage = 10;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public int $page = 1;

    public string $statusFilter = 'all';

    public string $dateFrom = '';

    public string $dateTo = '';

    public bool $showFilterDropdown = false;

    public bool $showDeleteModal = false;

    public $permitToDelete = null;

    protected array $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'statusFilter' => ['except' => 'all'],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $this->search = (string) request()->query('search', '');
        $this->page = (int) request()->query('page', 1);
        $this->statusFilter = (string) request()->query('statusFilter', 'all');
        $this->dateFrom = (string) request()->query('dateFrom', '');
        $this->dateTo = (string) request()->query('dateTo', '');

        $toast = session()->get('toast');
        if (is_array($toast) && isset($toast['message'])) {
            $this->dispatch('showToast', message: (string) $toast['message'], type: (string) ($toast['type'] ?? 'success'));
            session()->forget('toast');
        }
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
        $this->showFilterDropdown = !$this->showFilterDropdown;
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
            $this->sortDirection = 'desc';
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

    public function deletePermit(int $permitId): void
    {
        $permit = Permit::query()->find($permitId);
        if (!$permit) {
            $this->dispatch('showToast', message: 'Permit not found.', type: 'error');
            return;
        }

        $this->permitToDelete = $permit;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->permitToDelete = null;
    }

    public function confirmDeletePermit(): void
    {
        if (!$this->permitToDelete) {
            return;
        }

        $this->permitToDelete->delete();
        $this->closeDeleteModal();
        $this->dispatch('showToast', message: 'Permit deleted successfully!', type: 'success');
    }

    protected function baseQuery(): Builder
    {
        $permits = Permit::query()->with(['destinationLocation']);

        $search = trim($this->search);
        if ($search !== '') {
            $permits->where(function (Builder $query) use ($search) {
                $query
                    ->where('permit_id', 'like', '%' . $search . '%')
                    ->orWhere('area', 'like', '%' . $search . '%')
                    ->orWhere('names', 'like', '%' . $search . '%')
                    ->orWhereHas('destinationLocation', function (Builder $q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($this->statusFilter !== 'all') {
            $permits->where('status', (int) $this->statusFilter);
        }

        if ($this->dateFrom || $this->dateTo) {
            if ($this->dateFrom && $this->dateTo) {
                $permits->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
            } elseif ($this->dateFrom) {
                $permits->whereDate('created_at', '>=', $this->dateFrom);
            } elseif ($this->dateTo) {
                $permits->whereDate('created_at', '<=', $this->dateTo);
            }
        }

        return $permits;
    }

    public function getPaginationData(): array
    {
        $permits = $this->baseQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage, ['*'], 'page', $this->page);

        $currentPage = $permits->currentPage();
        $lastPage = $permits->lastPage();
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
            'permits' => $permits,
            'pages' => $pages,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
        ];
    }

    public function render()
    {
        return view('livewire.admin.permits.dashboard', $this->getPaginationData());
    }
}
