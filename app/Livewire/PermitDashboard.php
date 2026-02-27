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
        ];
        
        return $colors[$status] ?? 'gray';
    }

    public function render()
    {
        // Today's permits (in progress for today)
        $todayQuery = Permit::with(['farmLocation', 'destinationLocation', 'receivedBy'])
            ->whereDate('date_of_visit', Carbon::today())
            ->where('status', 1) // In Progress
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
