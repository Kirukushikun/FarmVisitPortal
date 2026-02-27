<?php

namespace App\Livewire\Admin\Permits;

use App\Models\Location;
use App\Models\Permit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    public int $currentStep = 1;

    /** @var int[] */
    public array $visibleStepIds = [1, 2, 3];

    public string $area = '';

    public string $farmLocationId = '';

    public string $names = '';

    public string $areaToVisit = '';

    public string $destinationLocationId = '';

    public string $dateOfVisit = '';

    public ?int $expectedDurationHours = null;

    public ?int $expectedDurationMinutes = null;

    public ?int $expectedDurationSeconds = null;

    public string $previousFarmLocationId = '';

    public string $dateOfVisitPreviousFarm = '';

    public string $purpose = '';

    public ?string $returnUrl = null;

    public Permit $permit;

    protected array $messages = [
        'required' => 'Please fill in this field.',
        'integer' => 'Please enter a valid number.',
        'string' => 'Please enter valid text.',
        'date' => 'Please select a valid date.',
        'before_or_equal' => 'Please select a valid date.',
        'max' => 'Please enter a valid value.',
        'min' => 'Please enter a valid value.',
        'exists' => 'Please select a valid option.',
        'not_in' => 'Please select a valid option.',

        'farmLocationId.required' => 'Please select a farm.',
        'farmLocationId.exists' => 'Please select a valid farm.',
        'destinationLocationId.required' => 'Please select a destination.',
        'destinationLocationId.exists' => 'Please select a valid destination.',
        'destinationLocationId.not_in' => 'Destination must be different from the farm.',
        'dateOfVisit.required' => 'Please select the date of visit.',

        'expectedDurationMinutes.max' => 'Minutes must be between 0 and 59.',
        'expectedDurationSeconds.max' => 'Seconds must be between 0 and 59.',
    ];

    protected array $validationAttributes = [
        'area' => 'area',
        'farmLocationId' => 'farm',
        'names' => 'names',
        'areaToVisit' => 'area to visit',
        'destinationLocationId' => 'destination',
        'dateOfVisit' => 'date of visit',
        'expectedDurationHours' => 'expected duration (hours)',
        'expectedDurationMinutes' => 'expected duration (minutes)',
        'expectedDurationSeconds' => 'expected duration (seconds)',
        'previousFarmLocationId' => 'previous farm visited',
        'dateOfVisitPreviousFarm' => 'previous farm visit date',
        'purpose' => 'purpose',
    ];

    public function mount(): void
    {
        // Get permit from route parameters
        $permitId = request()->route('permit');
        if ($permitId instanceof Permit) {
            $this->permit = $permitId;
        } else {
            $this->permit = Permit::findOrFail($permitId);
        }
        
        $this->populateForm();

        $return = request()->query('return');
        $isSafeReturn = is_string($return)
            && $return !== ''
            && str_starts_with($return, '/')
            && ! preg_match('/^\/livewire-[^\/]+\/update$/', $return);

        if ($isSafeReturn) {
            $this->returnUrl = $return;
        }
    }

    private function populateForm(): void
    {
        $this->area = $this->permit->area ?? '';
        $this->farmLocationId = (string) ($this->permit->farm_location_id ?? '');
        $this->names = $this->permit->names ?? '';
        $this->areaToVisit = $this->permit->area_to_visit ?? '';
        $this->destinationLocationId = (string) ($this->permit->destination_location_id ?? '');
        $this->dateOfVisit = $this->permit->date_of_visit?->format('Y-m-d') ?? '';
        
        // Parse duration
        $totalSeconds = $this->permit->expected_duration_seconds ?? 0;
        $this->expectedDurationHours = intdiv($totalSeconds, 3600);
        $this->expectedDurationMinutes = intdiv($totalSeconds % 3600, 60);
        $this->expectedDurationSeconds = $totalSeconds % 60;
        
        $this->previousFarmLocationId = (string) ($this->permit->previous_farm_location_id ?? '');
        $this->dateOfVisitPreviousFarm = $this->permit->date_of_visit_previous_farm?->format('Y-m-d') ?? '';
        $this->purpose = $this->permit->purpose ?? '';
    }

    public function nextStep(): void
    {
        if ($this->currentStep < (int) end($this->visibleStepIds)) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function updatedFarmLocationId(): void
    {
        if ($this->destinationLocationId !== '' && $this->destinationLocationId === $this->farmLocationId) {
            $this->destinationLocationId = '';
        }
    }

    public function submitForm(): mixed
    {
        $this->validate($this->rulesForSubmit());

        $originalDateOfVisit = $this->permit->date_of_visit?->format('Y-m-d');
        $durationSeconds = $this->calculateExpectedDurationSeconds();

        $newDateOfVisit = $this->dateOfVisit !== '' ? Carbon::parse($this->dateOfVisit) : null;
        $newDateOfVisitString = $newDateOfVisit?->format('Y-m-d');
        $isRescheduled = $originalDateOfVisit !== $newDateOfVisitString;

        // Update permit
        $this->permit->update([
            'area' => $this->area,
            'farm_location_id' => (int) $this->farmLocationId,
            'names' => $this->names,
            'area_to_visit' => $this->areaToVisit,
            'destination_location_id' => (int) $this->destinationLocationId,
            'date_of_visit' => $newDateOfVisit,
            'expected_duration_seconds' => $durationSeconds,
            'previous_farm_location_id' => $this->previousFarmLocationId !== '' ? (int) $this->previousFarmLocationId : null,
            'date_of_visit_previous_farm' => $this->dateOfVisitPreviousFarm !== '' ? Carbon::parse($this->dateOfVisitPreviousFarm) : null,
            'purpose' => $this->purpose !== '' ? $this->purpose : null,
            'completed_at' => $isRescheduled ? null : $this->permit->completed_at,
        ]);

        // Auto-update status based on date
        $this->updatePermitStatus();

        $permitId = (string) ($this->permit->permit_id ?? '');
        $suffix = $permitId !== '' ? " (" . $permitId . ")" : '';
        session()->flash('toast', [
            'message' => 'Permit has been updated successfully!' . $suffix,
            'type' => 'success',
        ]);

        if ($this->returnUrl) {
            return redirect()->to($this->returnUrl);
        }

        return redirect()->route('admin.permits.index');
    }

    private function updatePermitStatus(): void
    {
        if (!$this->permit->date_of_visit) {
            return;
        }

        $today = now()->startOfDay();
        $visitDate = $this->permit->date_of_visit->startOfDay();

        if ($visitDate->isSameDay($today)) {
            $this->permit->update([
                'status' => 1, // In Progress
                'completed_at' => null,
                'received_by' => null,
            ]);
        } elseif ($visitDate->isAfter($today)) {
            $this->permit->update([
                'status' => 0, // Scheduled
                'completed_at' => null,
                'received_by' => null,
            ]);
        } else {
            $this->permit->update([
                'status' => 2, // Completed
                'completed_at' => now(),
                'received_by' => (int) Auth::id(),
            ]);
        }
    }

    public function canProceed(): bool
    {
        return true;
    }

    public function isLastVisibleStep(): bool
    {
        return $this->currentStep === (int) end($this->visibleStepIds);
    }

    public function showProgress(): bool
    {
        return count($this->visibleStepIds) > 1;
    }

    public function getFarmLocationsProperty()
    {
        return Location::query()
            ->where('is_disabled', false)
            ->orderBy('name')
            ->get();
    }

    public function getDestinationLocationsProperty()
    {
        $query = Location::query()
            ->where('is_disabled', false)
            ->orderBy('name');

        $farmId = (int) $this->farmLocationId;
        if ($farmId > 0) {
            $query->where('id', '!=', $farmId);
        }

        return $query->get();
    }

    public function getPreviousFarmLocationsProperty()
    {
        return Location::query()
            ->where('is_disabled', false)
            ->orderBy('name')
            ->get();
    }

    protected function rulesForSubmit(): array
    {
        return [
            'area' => ['required', 'string', 'max:255'],
            'farmLocationId' => ['required', 'integer', 'exists:locations,id'],
            'names' => ['required', 'string'],
            'areaToVisit' => ['required', 'string'],
            'destinationLocationId' => ['required', 'integer', 'exists:locations,id'],
            'dateOfVisit' => ['required', 'date'],
            'expectedDurationHours' => ['nullable', 'integer', 'min:0'],
            'expectedDurationMinutes' => ['nullable', 'integer', 'min:0', 'max:59'],
            'expectedDurationSeconds' => ['nullable', 'integer', 'min:0', 'max:59'],
            'previousFarmLocationId' => ['nullable', 'integer', 'exists:locations,id'],
            'dateOfVisitPreviousFarm' => ['nullable', 'date', 'before_or_equal:today'],
            'purpose' => ['nullable', 'string'],
        ];
    }

    private function calculateExpectedDurationSeconds(): int
    {
        $hours = $this->expectedDurationHours ?? 0;
        $minutes = $this->expectedDurationMinutes ?? 0;
        $seconds = $this->expectedDurationSeconds ?? 0;

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    public function render()
    {
        return view('livewire.admin.permits.edit');
    }
}
