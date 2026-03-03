<?php

namespace App\Livewire\Admin\Permits;

use App\Models\Location;
use App\Models\Permit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public int $currentStep = 1;

    /** @var int[] */
    public array $visibleStepIds = [1, 2];

    public string $area = '';

    public string $farmLocationId = '';

    public string $names = '';


    public string $dateOfVisit = '';

    public ?float $expectedDurationHours = null;

    public string $previousFarmLocationId = '';

    public string $dateOfVisitPreviousFarm = '';

    public string $purpose = '';

    public ?string $returnUrl = null;

    protected array $messages = [
        'required' => 'Please fill in this field.',
        'integer' => 'Please enter a valid number.',
        'string' => 'Please enter valid text.',
        'date' => 'Please select a valid date.',
        'before_or_equal' => 'Please select a valid date.',
        'after_or_equal' => 'Please select a valid date.',
        'max' => 'Please enter a valid value.',
        'min' => 'Please enter a valid value.',
        'exists' => 'Please select a valid option.',
        'not_in' => 'Please select a valid option.',

        'farmLocationId.required' => 'Please select a farm.',
        'farmLocationId.exists' => 'Please select a valid farm.',
        'dateOfVisit.required' => 'Please select the date of visit.',

    ];

    protected array $validationAttributes = [
        'area' => 'area',
        'farmLocationId' => 'farm',
        'names' => 'names',
        'dateOfVisit' => 'date of visit',
        'expectedDurationHours' => 'expected duration (hours)',
        'previousFarmLocationId' => 'previous farm visited',
        'dateOfVisitPreviousFarm' => 'previous farm visit date',
        'purpose' => 'purpose',
    ];

    public function mount(): void
    {
        $return = request()->query('return');
        if (is_string($return) && $return !== '' && str_starts_with($return, '/')) {
            $this->returnUrl = $return;
        }
    }

    public function nextStep(): void
    {
        $this->validate($this->rulesForStep($this->currentStep));

        if ($this->currentStep < 2) {
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
        return;
    }

    public function submitForm(): mixed
    {
        $this->validate($this->rulesForSubmit());

        $durationHours = $this->calculateExpectedDurationHours();

        $visitDate = Carbon::parse($this->dateOfVisit)->startOfDay();
        $today = now()->startOfDay();

        $status = 0; // Scheduled
        $completedAt = null;
        $receivedBy = null;

        if ($visitDate->isSameDay($today)) {
            $status = 1; // In Progress
        } elseif ($visitDate->isAfter($today)) {
            $status = 0; // Scheduled
        } else {
            $status = 2; // Completed
            $completedAt = now();
            $receivedBy = (int) Auth::id();
        }

        $permit = Permit::create([
            'area' => $this->area,
            'farm_location_id' => (int) $this->farmLocationId,
            'names' => $this->names,
            'date_of_visit' => Carbon::parse($this->dateOfVisit),
            'expected_duration_hours' => $durationHours,
            'previous_farm_location_id' => $this->previousFarmLocationId !== '' ? (int) $this->previousFarmLocationId : null,
            'date_of_visit_previous_farm' => $this->dateOfVisitPreviousFarm !== '' ? Carbon::parse($this->dateOfVisitPreviousFarm) : null,
            'purpose' => $this->purpose !== '' ? $this->purpose : null,
            'status' => $status,
            'created_by' => (int) Auth::id(),
            'received_by' => $receivedBy,
            'completed_at' => $completedAt,
        ]);

        $permitId = (string) ($permit->permit_id ?? '');
        $suffix = $permitId !== '' ? ' (' . $permitId . ')' : '';
        session()->flash('toast', [
            'message' => 'Permit has been created successfully!' . $suffix,
            'type' => 'success',
        ]);

        if ($this->returnUrl) {
            return redirect()->to($this->returnUrl);
        }

        return redirect()->route('admin.permits.index');
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

    public function getPreviousFarmLocationsProperty()
    {
        return Location::query()
            ->where('is_disabled', false)
            ->orderBy('name')
            ->get();
    }

    protected function rulesForStep(int $step): array
    {
        if ($step === 1) {
            return [
                'area' => ['required', 'string', 'min:2', 'max:255'],
                'farmLocationId' => ['required', 'integer', Rule::exists('locations', 'id')],
                'names' => ['required', 'string', 'min:2'],
                'dateOfVisit' => ['required', 'date', 'after_or_equal:today'],
                'expectedDurationHours' => ['required', 'numeric', 'gt:0'],
            ];
        }

        if ($step === 2) {
            return [
                'previousFarmLocationId' => ['nullable', 'integer', Rule::exists('locations', 'id')],
                'dateOfVisitPreviousFarm' => ['nullable', 'date', 'before_or_equal:today'],
                'purpose' => ['nullable', 'string', 'min:2'],
            ];
        }

        return [];
    }

    protected function rulesForSubmit(): array
    {
        return array_merge(
            $this->rulesForStep(1),
            $this->rulesForStep(2),
        );
    }

    protected function calculateExpectedDurationHours(): ?float
    {
        $hours = (float) ($this->expectedDurationHours ?? 0);
        return $hours > 0 ? $hours : null;
    }

    public function render()
    {
        return view('livewire.admin.permits.create');
    }
}
