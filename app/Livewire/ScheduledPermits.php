<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Permit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ScheduledPermits extends Component
{
    public string $search = '';

    public int $perPage = 9;

    public int $page = 1;

    protected $listeners = ['refreshPermits' => '$refresh'];

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

    public function viewPermit($permitId)
    {
        return redirect()->route('user.permits.show', $permitId);
    }

    public function getStatusLabel($status)
    {
        $labels = [
            0 => 'Scheduled',
            1 => 'In Progress',
            2 => 'Completed',
            3 => 'Cancelled',
            4 => 'On Hold',
            5 => 'Returned',
            6 => 'Lapsed',
            7 => 'Resolved',
        ];
        
        return $labels[$status] ?? 'Unknown';
    }

    public function getStatusColor($status)
    {
        $colors = [
            0 => 'yellow',
            1 => 'blue',
            2 => 'green',
            3 => 'red',
            4 => 'orange',
            5 => 'purple',
            6 => 'yellow',
            7 => 'teal',
        ];
        
        return $colors[$status] ?? 'gray';
    }

    protected function baseQuery()
    {

        $farmLocationId = (int) session('selected_location_id', 0);

        $query = Permit::where('farm_location_id', $farmLocationId)
            ->whereDate('date_of_visit', '>=', Carbon::today())
            ->where('status', 0)
            ->orderBy('date_of_visit', 'asc');

        if ($this->search !== '') {
            $query->where('permit_id', 'like', '%' . $this->search . '%');
        }

        return $query;
    }

    public function getPaginationData(): array
    {
        $permits = $this->baseQuery()
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
        return view('livewire.scheduled-permits', $this->getPaginationData());
    }
}
