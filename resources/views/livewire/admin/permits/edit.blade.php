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
                    @php
                        $selectedFarmType = (int) (($this->farmLocations ?? collect())->firstWhere('id', (int) ($farmLocationId ?? 0))?->farm_type ?? 0);
                        $requiredDays = match($selectedFarmType) {
                            0 => 5,
                            1 => 3,
                            2 => null,
                            default => null,
                        };
                    @endphp

                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Visitor Groups <span class="text-red-500">*</span>
                    </label>

                    @foreach($namesGroups as $i => $group)
                        @php $isAlert = $this->groupAlerts[$i] ?? false; @endphp
                        <div class="border rounded-lg p-3 space-y-2 mb-2 transition-colors {{ $isAlert ? 'border-red-400 dark:border-red-500 bg-red-50 dark:bg-red-900/10' : 'border-gray-200 dark:border-gray-700' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Group {{ $i + 1 }}</span>
                                @if(count($namesGroups) > 1)
                                    <button type="button" wire:click="removeNamesGroup({{ $i }})"
                                        class="text-red-400 hover:text-red-600 text-xs cursor-pointer">
                                        Remove
                                    </button>
                                @endif
                            </div>

                            @if($isAlert)
                                <div class="flex items-center gap-2 px-3 py-2 rounded-md bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-600">
                                    <svg class="w-4 h-4 text-red-600 dark:text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-xs font-medium text-red-600 dark:text-red-400">
                                        🚨 Alert: This group has not met the required {{ $requiredDays }}-day interval since their last farm visit.
                                    </span>
                                </div>
                            @endif

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
                            <x-text-input
                                label="Previous Farm Visited (optional)"
                                name="namesGroups[{{ $i }}][previous_farm]"
                                type="text"
                                :wireModel="'namesGroups.' . $i . '.previous_farm'"
                                placeholder="e.g. San Pedro Farm"
                            />
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Visited (optional)</label>
                                <input
                                    type="date"
                                    wire:model.live="namesGroups.{{ $i }}.date_visited"
                                    max="{{ now()->toDateString() }}"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
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
                @if($namesMode === 'simple')
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
                            2 => 'No restrictions on previous farm visits.',
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
                @endif
            </div>
        </x-progress-navigation>
    </form>
</div>
