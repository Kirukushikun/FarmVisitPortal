<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Permit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class MyPermits extends Component
{
    use WithPagination;
    
    public $search = '';

    protected $listeners = ['refreshPermits' => '$refresh'];
    protected $paginationTheme = 'bootstrap';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function resetPage()
    {
        $this->reset();
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
        
        $query = Permit::with(['farmLocation', 'destinationLocation', 'receivedBy'])
            ->where('received_by', $user->id)
            ->whereIn('status', [3, 4]) // Completed or cancelled
            ->orderBy('date_of_visit', 'desc');

        // Only filter by location if user has location_id
        if ($user && isset($user->location_id)) {
            $query->where('destination_location_id', $user->location_id);
        }

        if ($this->search) {
            $query->where('permit_id', 'like', '%' . $this->search . '%');
        }

        $permits = $query->paginate(9);

        return view('livewire.my-permits', [
            'permits' => $permits,
        ]);
    }
}
