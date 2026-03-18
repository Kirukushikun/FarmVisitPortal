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

                {{-- Mode Toggle --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Visitor Entry Mode
                    </label>
                    <div class="inline-flex w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 p-1 gap-1">
                        <button type="button"
                            wire:click="switchNamesMode('simple')"
                            class="flex flex-1 justify-center items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 cursor-pointer
                                {{ $namesMode === 'simple'
                                    ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm'
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                            </svg>
                            Single Origin
                        </button>
                        <button type="button"
                            wire:click="switchNamesMode('detailed')"
                            class="flex flex-1 justify-center items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 cursor-pointer
                                {{ $namesMode === 'detailed'
                                    ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm'
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
                            </svg>
                            Multiple Origin
                        </button>
                    </div>
                </div>

                {{-- Simple Mode --}}
                @if($namesMode === 'simple')
                    <x-text-area
                        label="Visitor Names"
                        name="namesSimple"
                        error-key="namesSimple"
                        placeholder="Enter names"
                        wire:model.live="namesSimple"
                        required
                    />
                @endif

                {{-- Detailed Mode --}}
                @if($namesMode === 'detailed')
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Visitor Names <span class="text-red-500">*</span>
                    </label>

                    @foreach($namesGroups as $i => $group)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 space-y-2 mb-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Group {{ $i + 1 }}</span>
                                @if(count($namesGroups) > 1)
                                    <button type="button" wire:click="removeNamesGroup({{ $i }})"
                                        class="text-red-400 hover:text-red-600 text-xs cursor-pointer">
                                        Remove
                                    </button>
                                @endif
                            </div>
                            <x-text-input
                                label="Origin"
                                name="namesGroups[{{ $i }}][origin]"
                                type="text"
                                :wireModel="'namesGroups.' . $i . '.origin'"
                                placeholder="e.g. Laguna"
                                required
                            />
                            <x-text-area
                                label="Names"
                                name="namesGroups[{{ $i }}][names]"
                                error-key="namesGroups.{{ $i }}.names"
                                placeholder="Enter names from this origin"
                                wire:model.live="namesGroups.{{ $i }}.names"
                                required
                            />
                        </div>
                    @endforeach

                    <button type="button" wire:click="addNamesGroup"
                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline mt-1 cursor-pointer">
                        + Add another origin
                    </button>

                    @error('namesGroups')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                @endif

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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expected Duration (Hours)<span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
                        <div>
                            <x-text-input
                                label=""
                                name="expectedDurationHours"
                                type="number"
                                :wireModel="'expectedDurationHours'"
                                step="0.25"
                                min="0.25"
                                placeholder="Hours"
                                required
                            />
                        </div>
                    </div>
                </div>

                <x-text-area
                    label="Purpose"
                    name="purpose"
                    error-key="purpose"
                    placeholder="Enter purpose"
                    wire:model.live="purpose"
                    required
                />
            </div>

            <div data-step="2" class="space-y-4" @style(["display:none" => $currentStep !== 2])>
                <x-title>OPTIONAL DETAILS</x-title>

                <x-text-input
                    label="Previous Farm Visited"
                    name="previousFarmLocation"
                    type="text"
                    :wireModel="'previousFarmLocation'"
                    placeholder="Enter previous farm (optional)"
                />

                @php
                    $selectedFarmType = (int) (($this->farmLocations ?? collect())->firstWhere('id', (int) ($farmLocationId ?? 0))?->farm_type ?? 0);
                    $disclaimer = match ($selectedFarmType) {
                        0 => 'Must not have visited other Swine Farms 5 days prior to the Farm Visit.',
                        1 => 'Must not have visited other Poultry Farms 3 days prior to the Farm Visit.',
                        default => 'Must not have visited other Swine Farms 5 days prior to the Farm Visit.',
                    };
                @endphp
                @if ($disclaimer !== '')
                    <p class="text-xs text-gray-500 dark:text-gray-400" style="margin-top: -15px; margin-bottom: 20px;" >
                        {{ $disclaimer }}
                    </p>
                @endif

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
            </div>
        </x-progress-navigation>
    </form>
</div>
