<div x-data="{ formSubmitted: false }">
    <form wire:submit.prevent="submitForm" id="step-form" class="space-y-4" novalidate>
        @csrf

        <x-progress-navigation
            :current-step="$currentStep"
            :visible-step-ids="$visibleStepIds"
            :can-proceed="$this->canProceed()"
            :is-last-visible-step="$this->isLastVisibleStep()"
            :show-progress="$this->showProgress()"
        >
            <div data-step="1" class="space-y-4" @style(["display:none" => $currentStep !== 1])>
                <x-title>EDIT PERMIT {{ $permit->permit_id ?? '' }}</x-title>

                <x-dropdown
                    label="Farm"
                    name="farmLocationId"
                    error-key="farmLocationId"
                    placeholder="Select farm"
                    wire:model.live="farmLocationId"
                    required
                >
                    @foreach($this->farmLocations as $location)
                        <option value="{{ $location->id }}" {{ $farmLocationId == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                    @endforeach
                </x-dropdown>

                <x-dropdown
                    label="Area"
                    name="areaId"
                    error-key="areaId"
                    wire:model.live="areaId"
                    required
                >
                    <option value="" hidden selected>Select an area</option>
                    @foreach($this->areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </x-dropdown>

                <x-text-area
                    label="Names"
                    name="names"
                    error-key="names"
                    placeholder="Enter names"
                    wire:model.live="names"
                    required
                />

                <x-text-input
                    label="Date of Visit"
                    name="dateOfVisit"
                    type="date"
                    :wireModel="'dateOfVisit'"
                    :min="now()->toDateString()"
                    required
                >
                    <x-button type="button" wire:click="clearDateOfVisit" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer" title="Clear">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </x-button>
                </x-text-input>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expected Duration <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                        <div>
                            <x-text-input
                                label=""
                                name="expectedDurationHours"
                                type="number"
                                :wireModel="'expectedDurationHours'"
                                step="0.25"
                                min="0"
                                placeholder="Hours"
                                required
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>OPTIONAL DETAILS</x-title>

                <x-dropdown
                    label="Previous Farm Visited"
                    name="previousFarmLocationId"
                    error-key="previousFarmLocationId"
                    placeholder="Select previous farm (optional)"
                    wire:model.live="previousFarmLocationId"
                >
                    <option value="">None</option>
                    @foreach($this->previousFarmLocations as $location)
                        <option value="{{ $location->id }}" {{ $previousFarmLocationId == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                    @endforeach
                </x-dropdown>

                <x-text-input
                    label="Date of Visit"
                    name="dateOfVisitPreviousFarm"
                    type="date"
                    :wireModel="'dateOfVisitPreviousFarm'"
                    :max="now()->toDateString()"
                >
                    <x-button type="button" wire:click="clearPreviousFarmDate" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer" title="Clear">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </x-button>
                </x-text-input>

                <x-text-area
                    label="Purpose"
                    name="purpose"
                    error-key="purpose"
                    placeholder="Enter purpose"
                    wire:model.live="purpose"
                />
            </div>
        </x-progress-navigation>
    </form>
</div>
