<?php

namespace App\Livewire\Admin\Permits;

use App\Models\Permit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Dashboard extends Component
{
    public string $search = '';

    public int $perPage = 10;

    public string $sortField = 'date_of_visit';

    public string $sortDirection = 'desc';

    public int $page = 1;

    public array $statusFilter = [];

    public array $pendingStatusFilter = [];

    public string $status = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $pendingDateFrom = '';

    public string $pendingDateTo = '';

    public string $completedDateFrom = '';

    public string $completedDateTo = '';

    public string $visitDateFrom = '';

    public string $visitDateTo = '';

    public bool $showFilterDropdown = false;

    public bool $showDeleteModal = false;

    public bool $showOriginModal = false;

    public $permitToDelete = null;

    public bool $showRescheduleModal = false;

    public ?Permit $permitToReschedule = null;

    public string $rescheduleDateOfVisit = '';

    public string $returnUrl = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'status' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'completedDateFrom' => ['except' => ''],
        'completedDateTo' => ['except' => ''],
        'visitDateFrom' => ['except' => ''],
        'visitDateTo' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $this->returnUrl = (string) request()->getRequestUri();

        $this->search = (string) request()->query('search', '');
        $this->page = (int) request()->query('page', 1);
        $this->status = (string) request()->query('status', '');

        $allowed = ['0', '1', '2', '3', '4', '5'];
        $statusParts = array_filter(array_map('trim', explode(',', $this->status)), fn ($v) => $v !== '');
        $statusParts = array_values(array_unique($statusParts));
        $statusParts = array_values(array_filter($statusParts, fn ($v) => in_array((string) $v, $allowed, true)));
        $this->statusFilter = array_map(fn ($v) => (string) $v, $statusParts);
        $this->status = implode(',', $this->statusFilter);

        $this->pendingStatusFilter = $this->statusFilter;
        $this->dateFrom = (string) request()->query('dateFrom', '');
        $this->dateTo = (string) request()->query('dateTo', '');

        $this->pendingDateFrom = $this->dateFrom;
        $this->pendingDateTo = $this->dateTo;

        $this->completedDateFrom = (string) request()->query('completedDateFrom', '');
        $this->completedDateTo = (string) request()->query('completedDateTo', '');
        $this->visitDateFrom = (string) request()->query('visitDateFrom', '');
        $this->visitDateTo = (string) request()->query('visitDateTo', '');

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

    public function updatedStatusFilter(): void
    {
        $this->status = implode(',', $this->statusFilter);
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

    public function applyFilters(): void
    {
        $this->statusFilter = array_map(fn ($v) => (string) $v, $this->pendingStatusFilter);
        $this->status = implode(',', $this->statusFilter);

        $this->dateFrom = $this->pendingDateFrom;
        $this->dateTo = $this->pendingDateTo;

        if ($this->dateFrom && $this->dateTo && $this->dateFrom > $this->dateTo) {
            $this->dateTo = '';
            $this->pendingDateTo = '';
        }

        if ($this->completedDateFrom && $this->completedDateTo && $this->completedDateFrom > $this->completedDateTo) {
            $this->completedDateTo = '';
        }

        if ($this->visitDateFrom && $this->visitDateTo && $this->visitDateFrom > $this->visitDateTo) {
            $this->visitDateTo = '';
        }

        $this->page = 1;
        $this->showFilterDropdown = false;
    }

    public function resetFilters(): void
    {
        $this->statusFilter = [];
        $this->pendingStatusFilter = [];
        $this->status = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->completedDateFrom = '';
        $this->completedDateTo = '';
        $this->visitDateFrom = '';
        $this->visitDateTo = '';

        $this->pendingDateFrom = '';
        $this->pendingDateTo = '';
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

    public function openOriginModal(): void
    {
        $this->showOriginModal = true;
    }

    public function closeOriginModal(): void
    {
        $this->showOriginModal = false;
    }

    public function selectOriginMode(string $mode): void
    {
        $this->showOriginModal = false;
        $url = route('admin.permits.create', ['return' => $this->returnUrl, 'mode' => $mode]);
        $this->redirect($url);
    }

    public function reschedulePermit(int $permitId): void
    {
        $permit = Permit::query()->find($permitId);
        if (! $permit) {
            $this->dispatch('showToast', message: 'Permit not found.', type: 'error');
            return;
        }

        if ((int) ($permit->status ?? 0) !== 3) {
            $this->dispatch('showToast', message: 'Only cancelled permits can be rescheduled.', type: 'error');
            return;
        }

        $this->permitToReschedule = $permit;
        $this->rescheduleDateOfVisit = $permit->date_of_visit?->format('Y-m-d') ?? '';
        $this->resetValidation();
        $this->showRescheduleModal = true;
    }

    public function closeRescheduleModal(): void
    {
        $this->showRescheduleModal = false;
        $this->permitToReschedule = null;
        $this->rescheduleDateOfVisit = '';
        $this->resetValidation();
    }

    public function confirmReschedulePermit(): void
    {
        if (! $this->permitToReschedule) {
            return;
        }

        $this->validate([
            'rescheduleDateOfVisit' => ['required', 'date'],
        ]);

        $permit = Permit::query()->find((int) $this->permitToReschedule->id);
        if (! $permit) {
            $this->dispatch('showToast', message: 'Permit not found.', type: 'error');
            $this->closeRescheduleModal();
            return;
        }

        if ((int) ($permit->status ?? 0) !== 3) {
            $this->dispatch('showToast', message: 'Only cancelled permits can be rescheduled.', type: 'error');
            $this->closeRescheduleModal();
            return;
        }

        $newDateOfVisit = Carbon::parse($this->rescheduleDateOfVisit)->startOfDay();
        $today = now()->startOfDay();

        $status = 0;
        $completedAt = null;
        $receivedBy = null;

        if ($newDateOfVisit->isSameDay($today)) {
            $status = 1;
        } elseif ($newDateOfVisit->isAfter($today)) {
            $status = 0;
        } else {
            $status = 2;
            $completedAt = now();
            $receivedBy = (int) Auth::id();
        }

        $permit->update([
            'date_of_visit' => $newDateOfVisit,
            'status' => $status,
            'completed_at' => $completedAt,
            'received_by' => $receivedBy,
        ]);

        $permitId = (string) ($permit->permit_id ?? '');
        $suffix = $permitId !== '' ? " (" . $permitId . ")" : '';
        $this->closeRescheduleModal();
        $this->dispatch('showToast', message: 'Permit rescheduled successfully!' . $suffix, type: 'success');
    }

    protected function baseQuery(): Builder
    {
        $permits = Permit::query()->with(['receivedBy', 'farmLocation', 'createdBy']);

        $user = Auth::user();
        $allowedAllDepartments = ['PURCHASING', 'IT & SECURITY'];

        if ($user && (int) $user->user_type !== 2 && ! in_array($user->department, $allowedAllDepartments, true)) {
            $permits->where('department', $user->department);
        }

        $search = trim($this->search);
        if ($search !== '') {
            $permits->where(function (Builder $query) use ($search) {
                $query
                    ->where('permit_id', 'like', '%' . $search . '%')
                    ->orWhere('area', 'like', '%' . $search . '%')
                    ->orWhere('department', 'like', '%' . $search . '%')
                    ->orWhereHas('farmLocation', function (Builder $farmQuery) use ($search) {
                        $farmQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('createdBy', function (Builder $userQuery) use ($search) {
                        $userQuery->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('username', 'like', '%' . $search . '%');
                    });
            });
        }

        if (!empty($this->statusFilter)) {
            $statuses = array_map(fn ($v) => (int) $v, $this->statusFilter);
            $permits->whereIn('status', $statuses);
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

        if ($this->completedDateFrom || $this->completedDateTo) {
            if ($this->completedDateFrom && $this->completedDateTo) {
                $permits->whereBetween('completed_at', [$this->completedDateFrom . ' 00:00:00', $this->completedDateTo . ' 23:59:59']);
            } elseif ($this->completedDateFrom) {
                $permits->whereDate('completed_at', '>=', $this->completedDateFrom);
            } elseif ($this->completedDateTo) {
                $permits->whereDate('completed_at', '<=', $this->completedDateTo);
            }
        }

        if ($this->visitDateFrom || $this->visitDateTo) {
            if ($this->visitDateFrom && $this->visitDateTo) {
                $permits->whereBetween('date_of_visit', [$this->visitDateFrom, $this->visitDateTo]);
            } elseif ($this->visitDateFrom) {
                $permits->whereDate('date_of_visit', '>=', $this->visitDateFrom);
            } elseif ($this->visitDateTo) {
                $permits->whereDate('date_of_visit', '<=', $this->visitDateTo);
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
