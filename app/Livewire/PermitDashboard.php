<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Permit;
use Carbon\Carbon;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class PermitDashboard extends Component
{
    use WithPagination;
    
    public $search = '';

    protected $listeners = ['refreshPermits' => '$refresh'];
    protected $paginationTheme = 'bootstrap';

    public function updatedSearch()
    {
        $this->resetPage();
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

    public function render()
    {
        $farmLocationId = (int) session('selected_location_id', 0);

        $todayQuery = Permit::where('farm_location_id', $farmLocationId)
            ->whereDate('date_of_visit', Carbon::today())
            ->whereIn('status', [1, 4]) // In Progress + On Hold
            ->orderBy('date_of_visit', 'asc');

        if ($this->search) {
            $todayQuery->where('permit_id', 'like', '%' . $this->search . '%');
        }


        $todayPermits = $todayQuery->paginate(9);

        return view('livewire.permit-dashboard', [
            'todayPermits' => $todayPermits,
        ]);
    }
}
