<?php

namespace App\Livewire\Admin\Permits;

use App\Models\Area;
use App\Models\Location;
use App\Models\Permit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Edit extends Component
{
    public int $currentStep = 1;

    /** @var int[] */
    public array $visibleStepIds = [1, 2];

    public int $areaId = 0;

    public string $farmLocationId = '';

    public string $names = '';


    public string $dateOfVisit = '';

    public string $expectedDurationHours = '';

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
        $this->areaId = (int) ($this->permit->area_id ?? 0);
        $this->farmLocationId = (string) ($this->permit->farm_location_id ?? '');
        $this->names = $this->permit->names ?? '';
        $this->dateOfVisit = $this->permit->date_of_visit?->format('Y-m-d') ?? '';
        
        $this->expectedDurationHours = (string) ($this->permit->expected_duration_hours ?? '');
        
        $this->previousFarmLocationId = (string) ($this->permit->previous_farm_location_id ?? '');
        $this->dateOfVisitPreviousFarm = $this->permit->date_of_visit_previous_farm?->format('Y-m-d') ?? '';
        $this->purpose = $this->permit->purpose ?? '';
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

    public function submitForm(): mixed
    {
        try {
            $this->validate($this->rulesForSubmit());

            $originalDateOfVisit = $this->permit->date_of_visit?->format('Y-m-d');
            $durationHours = $this->calculateExpectedDurationHoursForSubmit();

            $newDateOfVisit = $this->dateOfVisit !== '' ? Carbon::parse($this->dateOfVisit) : null;
            $newDateOfVisitString = $newDateOfVisit?->format('Y-m-d');
            $isRescheduled = $originalDateOfVisit !== $newDateOfVisitString;

            // Update permit
            $this->permit->update([
                'area_id' => $this->areaId,
                'farm_location_id' => (int) $this->farmLocationId,
                'names' => $this->names,
                'date_of_visit' => $newDateOfVisit,
                'expected_duration_hours' => $durationHours,
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            // On validation failure, determine which step has the error and go to that step
            $failedFields = array_keys($e->validator->failed());
            
            // Step 1 fields: areaId, farmLocationId, names, dateOfVisit, expectedDurationHours
            $step1Fields = ['areaId', 'farmLocationId', 'names', 'dateOfVisit', 'expectedDurationHours'];
            
            // Check if any step 1 fields failed
            foreach ($step1Fields as $field) {
                if (in_array($field, $failedFields)) {
                    $this->currentStep = 1;
                    throw $e; // Re-throw to show validation errors
                }
            }
            
            // Otherwise go to step 2
            $this->currentStep = 2;
            throw $e; // Re-throw to show validation errors
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Permit edit failed: ' . $e->getMessage(), [
                'permit_id' => $this->permit->id,
                'error' => $e->getTraceAsString()
            ]);
            
            // Flash error message to user
            session()->flash('toast', [
                'message' => 'Failed to update permit. Please try again.',
                'type' => 'error',
            ]);
            
            return null;
        }
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

    public function getPreviousFarmLocationsProperty()
    {
        return Location::query()
            ->where('is_disabled', false)
            ->orderBy('name')
            ->get();
    }

    protected function rulesForSubmit(): array
    {
        $rules = [
            'areaId' => ['required', 'integer', 'exists:areas,id'],
            'farmLocationId' => ['required', 'integer', 'exists:locations,id'],
            'names' => ['required', 'string'],
            'expectedDurationHours' => ['required', 'numeric', 'gt:0'],
            'previousFarmLocationId' => ['nullable', 'integer', 'exists:locations,id'],
            'dateOfVisitPreviousFarm' => ['nullable', 'date', 'before_or_equal:today'],
            'purpose' => ['nullable', 'string'],
        ];

        // For dateOfVisit, allow past dates if the permit already has a past date
        // but don't allow setting future dates to past dates
        if ($this->permit->date_of_visit && $this->permit->date_of_visit->isPast()) {
            // Permit has past date - allow any date (including past)
            $rules['dateOfVisit'] = ['required', 'date'];
        } else {
            // Permit has current/future date - don't allow setting to past
            $rules['dateOfVisit'] = ['required', 'date', 'after_or_equal:today'];
        }

        return $rules;
    }

    private function calculateExpectedDurationHours(): ?float
    {
        $hours = (float) ($this->expectedDurationHours ?? 0);
        return $hours > 0 ? $hours : null;
    }

    private function calculateExpectedDurationHoursForSubmit(): ?float
    {
        $hours = (float) ($this->expectedDurationHours ?? 0);
        return $hours > 0 ? $hours : null;
    }

    public function render()
    {
        return view('livewire.admin.permits.edit');
    }
}
