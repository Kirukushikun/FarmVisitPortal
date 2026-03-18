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

    public string $previousFarmLocation = '';

    public string $dateOfVisitPreviousFarm = '';

    public string $purpose = '';

    public ?string $returnUrl = null;

    public string $namesMode = 'simple';
    public string $namesSimple = '';
    public array $namesGroups = [
        ['origin' => '', 'names' => ''],
    ];

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
        'purpose.required' => 'Please enter the purpose of the visit.'
    ];

    protected array $validationAttributes = [
        'areaId' => 'area',
        'farmLocationId' => 'farm',
        'namesSimple' => 'visitor names',
        'namesGroups.*.origin' => 'origin',
        'namesGroups.*.names' => 'names',
        'dateOfVisit' => 'date of visit',
        'expectedDurationHours' => 'expected duration (hours)',
        'previousFarmLocation' => 'previous farm visited',
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
        $this->dateOfVisit = $this->permit->date_of_visit?->format('Y-m-d') ?? '';
        $this->expectedDurationHours = (string) ($this->permit->expected_duration_hours ?? '');
        $this->previousFarmLocation = (string) ($this->permit->previous_farm_location ?? '');
        $this->dateOfVisitPreviousFarm = $this->permit->date_of_visit_previous_farm?->format('Y-m-d') ?? '';
        $this->purpose = $this->permit->purpose ?? '';

        // Hydrate names
        $raw = $this->permit->names ?? '';
        $decoded = is_array($raw) ? $raw : json_decode($raw, true);

        if (is_array($decoded) && isset($decoded['mode'])) {
            $this->namesMode = $decoded['mode'];
            if ($decoded['mode'] === 'detailed') {
                $this->namesGroups = $decoded['groups'] ?? [['origin' => '', 'names' => '']];
            } else {
                $this->namesSimple = $decoded['value'] ?? '';
            }
        } else {
            // Legacy plain string — fall back to simple mode
            $this->namesMode = 'simple';
            $this->namesSimple = $raw;
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
                'names' => $this->buildNamesPayload(),
                'date_of_visit' => $newDateOfVisit,
                'expected_duration_hours' => $durationHours,
                'previous_farm_location' => trim($this->previousFarmLocation) !== '' ? trim($this->previousFarmLocation) : null,
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
            $failedFields = array_keys($e->validator->failed());
            $this->currentStep = $this->stepForFailedFields($failedFields);
            throw $e;
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

    protected function stepForFailedFields(array $failedFields): int
    {
        $step1Fields = ['areaId', 'farmLocationId', 'namesSimple', 'namesGroups', 'dateOfVisit', 'expectedDurationHours'];
        foreach ($step1Fields as $field) {
            if (in_array($field, $failedFields, true)) {
                return 1;
            }
        }

        $step2Fields = ['previousFarmLocation', 'dateOfVisitPreviousFarm', 'purpose'];
        foreach ($step2Fields as $field) {
            if (in_array($field, $failedFields, true)) {
                return 2;
            }
        }

        return 1;
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

    public function addNamesGroup(): void
    {
        $this->namesGroups[] = ['origin' => '', 'names' => ''];
    }

    public function removeNamesGroup(int $index): void
    {
        if (count($this->namesGroups) > 1) {
            array_splice($this->namesGroups, $index, 1);
            $this->namesGroups = array_values($this->namesGroups);
        }
    }

    public function switchNamesMode(string $mode): void
    {
        $this->namesMode = $mode;
        $this->resetValidation();
    }

    protected function buildNamesPayload(): string
    {
        if ($this->namesMode === 'detailed') {
            return json_encode([
                'mode' => 'detailed',
                'groups' => array_map(fn($g) => [
                    'origin' => trim($g['origin']),
                    'names' => trim($g['names']),
                ], $this->namesGroups),
            ]);
        }

        return json_encode([
            'mode' => 'simple',
            'value' => trim($this->namesSimple),
        ]);
    }

    protected function rulesForSubmit(): array
    {
        $rules = [
            'areaId' => ['required', 'integer', 'exists:areas,id'],
            'farmLocationId' => ['required', 'integer', 'exists:locations,id'],
            'namesSimple' => ['required_if:namesMode,simple', 'nullable', 'string', 'min:2'],
            'namesGroups' => ['required_if:namesMode,detailed', 'nullable', 'array', 'min:1'],
            'namesGroups.*.origin' => ['required_if:namesMode,detailed', 'nullable', 'string', 'min:2'],
            'namesGroups.*.names' => ['required_if:namesMode,detailed', 'nullable', 'string', 'min:2'],
            'expectedDurationHours' => ['required', 'numeric', 'gt:0'],
            'previousFarmLocation' => ['nullable', 'string', 'min:2'],
            'dateOfVisitPreviousFarm' => ['nullable', 'date', 'before_or_equal:today'],
            'purpose' => ['required', 'string', 'min:2'],
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
