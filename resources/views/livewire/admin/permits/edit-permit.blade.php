<div>
    @if ($showEditModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <div class="fixed inset-0 bg-black/50" wire:click="closeEditModal"></div>

            <div class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Edit Permit</h3>
                    <button type="button" wire:click="closeEditModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="updatePermit" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-text-input
                                label="Area"
                                name="area"
                                type="text"
                                :wireModel="'editArea'"
                                placeholder="Enter area"
                            />
                        </div>

                        <div>
                            <x-dropdown
                                label="Farm"
                                name="farmLocationId"
                                error-key="editFarmLocationId"
                                placeholder="Select farm"
                                wire:model.live="editFarmLocationId"
                                required
                            >
                                @foreach($farmLocations as $location)
                                    <option value="{{ $location->id }}" {{ $editFarmLocationId == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                @endforeach
                            </x-dropdown>
                        </div>
                    </div>

                    <div>
                        <x-text-area
                            label="Names"
                            name="names"
                            error-key="editNames"
                            placeholder="Enter names"
                            wire:model.live="editNames"
                            required
                        />
                    </div>

                    <div>
                        <x-text-area
                            label="Area/Section/Department to Visit"
                            name="areaToVisit"
                            error-key="editAreaToVisit"
                            placeholder="Enter area/section/department to visit"
                            wire:model.live="editAreaToVisit"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-dropdown
                                label="Destination"
                                name="destinationLocationId"
                                error-key="editDestinationLocationId"
                                placeholder="Select destination"
                                wire:model.live="editDestinationLocationId"
                                required
                            >
                                @foreach($destinationLocations as $location)
                                    <option value="{{ $location->id }}" {{ $editDestinationLocationId == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                @endforeach
                            </x-dropdown>
                        </div>

                        <div>
                            <x-text-input
                                label="Date of Visit"
                                name="dateOfVisit"
                                type="date"
                                :wireModel="'editDateOfVisit'"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expected Duration</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-text-input
                                    label=""
                                    name="expectedDurationHours"
                                    type="number"
                                    :wireModel="'editExpectedDurationHours'"
                                    placeholder="Hours"
                                />
                            </div>
                            <div>
                                <x-text-input
                                    label=""
                                    name="expectedDurationMinutes"
                                    type="number"
                                    :wireModel="'editExpectedDurationMinutes'"
                                    placeholder="Minutes"
                                />
                            </div>
                            <div>
                                <x-text-input
                                    label=""
                                    name="expectedDurationSeconds"
                                    type="number"
                                    :wireModel="'editExpectedDurationSeconds'"
                                    placeholder="Seconds"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-dropdown
                                label="Previous Farm Visited"
                                name="previousFarmLocationId"
                                error-key="editPreviousFarmLocationId"
                                placeholder="Select previous farm (optional)"
                                wire:model.live="editPreviousFarmLocationId"
                            >
                                <option value="">None</option>
                                @foreach($previousFarmLocations as $location)
                                    <option value="{{ $location->id }}" {{ $editPreviousFarmLocationId == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                @endforeach
                            </x-dropdown>
                        </div>

                        <div>
                            <x-text-input
                                label="Previous Farm Date of Visit"
                                name="dateOfVisitPreviousFarm"
                                type="date"
                                :wireModel="'editDateOfVisitPreviousFarm'"
                                :max="now()->toDateString()"
                            />
                        </div>
                    </div>

                    <div>
                        <x-text-area
                            label="Purpose"
                            name="purpose"
                            error-key="editPurpose"
                            placeholder="Enter purpose"
                            wire:model.live="editPurpose"
                        />
                    </div>

                    <div class="flex justify-end space-x-3">
                        <x-button
                            variant="outline-secondary"
                            size="sm"
                            wire:click="closeEditModal"
                            wire:loading.attr="disabled"
                            wire:target="closeEditModal"
                        >
                            <span wire:target="closeEditModal">Cancel</span>
                        </x-button>
                        <x-button
                            variant="primary"
                            size="sm"
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="updatePermit"
                        >
                            <span wire:loading.remove wire:target="updatePermit">Update Permit</span>
                            <span wire:loading wire:target="updatePermit">Updating...</span>
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
