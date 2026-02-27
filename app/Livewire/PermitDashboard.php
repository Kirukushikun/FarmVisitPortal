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
            1 => 'Pending',
            2 => 'In Progress',
            3 => 'Completed',
            4 => 'Cancelled',
        ];
        
        return $labels[$status] ?? 'Unknown';
    }

    public function getStatusColor($status)
    {
        $colors = [
            1 => 'yellow',
            2 => 'blue',
            3 => 'green',
            4 => 'red',
        ];
        
        return $colors[$status] ?? 'gray';
    }

    public function render()
    {
        $user = Auth::user();
        
        // Today's permits (in progress for today)
        $todayQuery = Permit::with(['farmLocation', 'destinationLocation', 'receivedBy'])
            ->whereDate('date_of_visit', Carbon::today())
            ->where('status', 2) // Only in-progress permits
            ->orderBy('date_of_visit', 'asc');

        if ($this->search) {
            $todayQuery->where('permit_id', 'like', '%' . $this->search . '%');
        }

        $todayPermits = $todayQuery->paginate(9);

        // Scheduled permits (future dates for user's location if available)
        $scheduledQuery = Permit::with(['farmLocation', 'destinationLocation', 'receivedBy'])
            ->whereDate('date_of_visit', '>', Carbon::today())
            ->whereIn('status', [1, 2]) // Pending or in-progress
            ->orderBy('date_of_visit', 'asc');

        // Only filter by location if user has location_id
        if ($user && isset($user->location_id)) {
            $scheduledQuery->where('destination_location_id', $user->location_id);
        }

        if ($this->search) {
            $scheduledQuery->where('permit_id', 'like', '%' . $this->search . '%');
        }

        $scheduledPermits = $scheduledQuery->paginate(9);

        // My permits (received by current user at their location if available)
        $myQuery = Permit::with(['farmLocation', 'destinationLocation', 'receivedBy'])
            ->where('received_by', $user->id)
            ->whereIn('status', [3, 4]) // Completed or cancelled
            ->orderBy('date_of_visit', 'desc');

        // Only filter by location if user has location_id
        if ($user && isset($user->location_id)) {
            $myQuery->where('destination_location_id', $user->location_id);
        }

        if ($this->search) {
            $myQuery->where('permit_id', 'like', '%' . $this->search . '%');
        }

        $myPermits = $myQuery->paginate(9);

        // Cancelled permits (past due date, not received, for user's location if available)
        $cancelledQuery = Permit::with(['farmLocation', 'destinationLocation', 'receivedBy'])
            ->where('status', 4) // Cancelled
            ->whereNull('received_by') // Not received by anyone
            ->whereDate('date_of_visit', '<', Carbon::today())
            ->orderBy('date_of_visit', 'desc');

        // Only filter by location if user has location_id
        if ($user && isset($user->location_id)) {
            $cancelledQuery->where('destination_location_id', $user->location_id);
        }

        if ($this->search) {
            $cancelledQuery->where('permit_id', 'like', '%' . $this->search . '%');
        }

        $cancelledPermits = $cancelledQuery->paginate(9);

        return view('livewire.permit-dashboard', [
            'todayPermits' => $todayPermits,
            'scheduledPermits' => $scheduledPermits,
            'myPermits' => $myPermits,
            'cancelledPermits' => $cancelledPermits,
        ]);
    }
}
