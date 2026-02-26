<div>
    @if ($showModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>

            <div class="relative w-full max-w-md p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Edit Location</h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="updateLocation">
                    <div class="mb-6">
                        <x-text-input
                            label="Location Name"
                            name="name"
                            type="text"
                            :wireModel="'name'"
                            placeholder="Enter location name"
                        />
                    </div>

                    <div class="flex justify-end space-x-3">
                        <x-button
                            variant="outline-secondary"
                            size="sm"
                            wire:click="closeModal"
                            wire:loading.attr="disabled"
                            wire:target="closeModal"
                        >
                            <span wire:target="closeModal">Cancel</span>
                        </x-button>
                        <x-button
                            variant="primary"
                            size="sm"
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="updateLocation"
                        >
                            <span wire:loading.remove wire:target="updateLocation">Update Location</span>
                            <span wire:loading wire:target="updateLocation">Updating...</span>
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
