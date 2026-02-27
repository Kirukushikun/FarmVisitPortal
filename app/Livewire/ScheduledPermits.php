<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Permit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class ScheduledPermits extends Component
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
        $user = Auth::user();
        
        $query = Permit::with(['farmLocation', 'destinationLocation', 'receivedBy'])
            ->whereDate('date_of_visit', '>', Carbon::today())
            ->where('status', 0) // Scheduled
            ->orderBy('date_of_visit', 'asc');

        // Only filter by location if user has location_id
        if ($user && isset($user->location_id)) {
            $query->where('destination_location_id', $user->location_id);
        }

        if ($this->search) {
            $query->where('permit_id', 'like', '%' . $this->search . '%');
        }

        $permits = $query->paginate(9);

        return view('livewire.scheduled-permits', [
            'permits' => $permits,
        ]);
    }
}
