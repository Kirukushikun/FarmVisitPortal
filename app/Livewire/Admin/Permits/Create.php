<?php

namespace App\Livewire\Admin\Permits;

use App\Models\Area;
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

    public int $areaId = 0;

    public string $farmLocationId = '';

    public string $names = '';


    public string $dateOfVisit = '';

    public ?float $expectedDurationHours = null;

    public string $previousFarmLocation = '';

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
        'areaId' => 'area',
        'farmLocationId' => 'farm',
        'names' => 'names',
        'dateOfVisit' => 'date of visit',
        'expectedDurationHours' => 'expected duration (hours)',
        'previousFarmLocation' => 'previous farm visited',
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
        if ($this->currentStep < 2) {
            $this->resetValidation();
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->resetValidation();
            $this->currentStep--;
        }
    }

    public function updatedFarmLocationId(): void
    {
        // Reset area when farm changes
        $this->areaId = 0;
    }

    public function clearDateOfVisit(): void
    {
        $this->dateOfVisit = '';
    }

    public function clearPreviousFarmDate(): void
    {
        $this->dateOfVisitPreviousFarm = '';
    }

    public function submitForm(): mixed
    {
        try {
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
                'area_id' => $this->areaId,
                'farm_location_id' => (int) $this->farmLocationId,
                'names' => $this->names,
                'date_of_visit' => Carbon::parse($this->dateOfVisit),
                'expected_duration_hours' => $durationHours,
                'previous_farm_location' => trim($this->previousFarmLocation) !== '' ? trim($this->previousFarmLocation) : null,
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            $failedFields = array_keys($e->validator->failed());
            $this->currentStep = $this->stepForFailedFields($failedFields);
            throw $e;
        }
    }

    protected function stepForFailedFields(array $failedFields): int
    {
        $step1Fields = array_keys($this->rulesForStep(1));
        foreach ($step1Fields as $field) {
            if (in_array($field, $failedFields, true)) {
                return 1;
            }
        }

        $step2Fields = array_keys($this->rulesForStep(2));
        foreach ($step2Fields as $field) {
            if (in_array($field, $failedFields, true)) {
                return 2;
            }
        }

        return 1;
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

    public function getAreasProperty()
    {
        if (empty($this->farmLocationId)) {
            return collect();
        }

        return Area::query()
            ->where('location_id', (int) $this->farmLocationId)
            ->where('is_disabled', false)
            ->orderBy('name')
            ->get();
    }

    public function getFarmLocationsProperty()
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
                'areaId' => ['required', 'integer', Rule::exists('areas', 'id')->where(function ($query) {
                    $query->where('location_id', (int) $this->farmLocationId)
                          ->where('is_disabled', false);
                })],
                'farmLocationId' => ['required', 'integer', Rule::exists('locations', 'id')],
                'names' => ['required', 'string', 'min:2'],
                'dateOfVisit' => ['required', 'date', 'after_or_equal:today'],
                'expectedDurationHours' => ['required', 'numeric', 'gt:0'],
            ];
        }

        if ($step === 2) {
            return [
                'previousFarmLocation' => ['nullable', 'string', 'min:2'],
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
