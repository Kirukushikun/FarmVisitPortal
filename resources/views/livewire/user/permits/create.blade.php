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
                <x-title>CREATE PERMIT</x-title>

                <x-text-input
                    label="Area"
                    name="area"
                    type="text"
                    :wireModel="'area'"
                    placeholder="Enter area"
                />

                <x-dropdown
                    label="Farm"
                    name="farmLocationId"
                    error-key="farmLocationId"
                    placeholder="Select farm"
                    wire:model.live="farmLocationId"
                    required
                >
                    @foreach($this->farmLocations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
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

                <x-text-area
                    label="Area/Section/Department to Visit"
                    name="areaToVisit"
                    error-key="areaToVisit"
                    placeholder="Enter area/section/department to visit"
                    wire:model.live="areaToVisit"
                    required
                />
            </div>

            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>DESTINATION</x-title>

                <x-dropdown
                    label="Destination"
                    name="destinationLocationId"
                    error-key="destinationLocationId"
                    placeholder="Select destination"
                    wire:model.live="destinationLocationId"
                    required
                >
                    @foreach($this->destinationLocations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </x-dropdown>

                <x-text-input
                    label="Date of Visit"
                    name="dateOfVisit"
                    type="date"
                    :wireModel="'dateOfVisit'"
                    :min="now()->toDateString()"
                />

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expected Duration</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <x-text-input
                                label=""
                                name="expectedDurationHours"
                                type="number"
                                :wireModel="'expectedDurationHours'"
                                placeholder="Hours"
                            />
                        </div>
                        <div>
                            <x-text-input
                                label=""
                                name="expectedDurationMinutes"
                                type="number"
                                :wireModel="'expectedDurationMinutes'"
                                placeholder="Minutes"
                            />
                        </div>
                        <div>
                            <x-text-input
                                label=""
                                name="expectedDurationSeconds"
                                type="number"
                                :wireModel="'expectedDurationSeconds'"
                                placeholder="Seconds"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div data-step="3" class="space-y-4" @style(["display:none" => $currentStep !== 3])>
                <x-title>PREVIOUS FARM</x-title>

                <x-dropdown
                    label="Previous Farm Visited"
                    name="previousFarmLocationId"
                    error-key="previousFarmLocationId"
                    placeholder="Select previous farm (optional)"
                    wire:model.live="previousFarmLocationId"
                >
                    @foreach($this->previousFarmLocations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </x-dropdown>

                <x-text-input
                    label="Date of Visit"
                    name="dateOfVisitPreviousFarm"
                    type="date"
                    :wireModel="'dateOfVisitPreviousFarm'"
                    :max="now()->toDateString()"
                />

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
