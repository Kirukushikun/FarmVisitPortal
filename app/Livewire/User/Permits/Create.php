<?php

namespace App\Livewire\User\Permits;

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

        if ($this->currentStep < 3) {
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

        $durationSeconds = $this->calculateExpectedDurationSeconds();

        $permit = Permit::create([
            'area' => $this->area,
            'farm_location_id' => (int) $this->farmLocationId,
            'names' => $this->names,
            'area_to_visit' => $this->areaToVisit,
            'destination_location_id' => (int) $this->destinationLocationId,
            'date_of_visit' => Carbon::parse($this->dateOfVisit),
            'expected_duration_seconds' => $durationSeconds,
            'previous_farm_location_id' => $this->previousFarmLocationId !== '' ? (int) $this->previousFarmLocationId : null,
            'date_of_visit_previous_farm' => $this->dateOfVisitPreviousFarm !== '' ? Carbon::parse($this->dateOfVisitPreviousFarm) : null,
            'purpose' => $this->purpose !== '' ? $this->purpose : null,
            'status' => 0,
            'created_by' => (int) Auth::id(),
            'received_by' => null,
        ]);

        unset($permit);

        $this->dispatch('showToast', message: 'Permit has been created successfully!', type: 'success');

        if ($this->returnUrl) {
            return redirect()->to($this->returnUrl);
        }

        return redirect()->route('user.home');
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

    protected function rulesForStep(int $step): array
    {
        if ($step === 1) {
            return [
                'area' => ['required', 'string', 'min:2', 'max:255'],
                'farmLocationId' => ['required', 'integer', Rule::exists('locations', 'id')],
                'names' => ['required', 'string', 'min:2'],
                'areaToVisit' => ['required', 'string', 'min:2'],
            ];
        }

        if ($step === 2) {
            $farmId = (int) $this->farmLocationId;
            return [
                'destinationLocationId' => [
                    'required',
                    'integer',
                    Rule::exists('locations', 'id'),
                    Rule::notIn([$farmId]),
                ],
                'dateOfVisit' => ['required', 'date'],
                'expectedDurationHours' => ['nullable', 'integer', 'min:0'],
                'expectedDurationMinutes' => ['nullable', 'integer', 'min:0', 'max:59'],
                'expectedDurationSeconds' => ['nullable', 'integer', 'min:0', 'max:59'],
            ];
        }

        return [
            'previousFarmLocationId' => ['nullable', 'integer', Rule::exists('locations', 'id')],
            'dateOfVisitPreviousFarm' => ['nullable', 'date', 'before_or_equal:today'],
            'purpose' => ['nullable', 'string', 'min:2'],
        ];
    }

    protected function rulesForSubmit(): array
    {
        return array_merge(
            $this->rulesForStep(1),
            $this->rulesForStep(2),
            $this->rulesForStep(3),
        );
    }

    protected function calculateExpectedDurationSeconds(): ?int
    {
        $hours = (int) ($this->expectedDurationHours ?? 0);
        $minutes = (int) ($this->expectedDurationMinutes ?? 0);
        $seconds = (int) ($this->expectedDurationSeconds ?? 0);

        $total = ($hours * 3600) + ($minutes * 60) + $seconds;

        return $total > 0 ? $total : null;
    }

    public function render()
    {
        return view('livewire.user.permits.create');
    }
}
