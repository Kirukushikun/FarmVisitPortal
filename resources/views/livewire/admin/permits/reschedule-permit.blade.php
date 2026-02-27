<div>
    @if ($showRescheduleModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <div class="fixed inset-0 bg-black/50" wire:click="closeRescheduleModal"></div>

            <div class="relative w-full max-w-md p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Reschedule Permit</h3>
                    <button type="button" wire:click="closeRescheduleModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Permit:
                        <span class="font-medium text-gray-900 dark:text-white">{{ $permitToReschedule->permit_id ?? '' }}</span>
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Date of Visit</label>
                    <input
                        type="date"
                        wire:model="rescheduleDateOfVisit"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent dark:bg-gray-700 dark:text-white"
                    />
                    @error('rescheduleDateOfVisit')
                        <p class="text-xs text-red-600 dark:text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <x-button
                        variant="outline-secondary"
                        size="sm"
                        wire:click="closeRescheduleModal"
                        wire:loading.attr="disabled"
                        wire:target="closeRescheduleModal"
                    >
                        <span wire:target="closeRescheduleModal">Cancel</span>
                    </x-button>
                    <x-button
                        variant="primary"
                        size="sm"
                        wire:click="confirmReschedulePermit"
                        wire:loading.attr="disabled"
                        wire:target="confirmReschedulePermit"
                    >
                        <span wire:loading.remove wire:target="confirmReschedulePermit">Save</span>
                        <span wire:loading wire:target="confirmReschedulePermit">Saving...</span>
                    </x-button>
                </div>
            </div>
        </div>
    @endif
</div>
