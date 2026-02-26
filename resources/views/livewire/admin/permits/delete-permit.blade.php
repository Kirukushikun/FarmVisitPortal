<div>
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <div class="fixed inset-0 bg-black/50" wire:click="closeDeleteModal"></div>

            <div class="relative w-full max-w-md p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delete Permit</h3>
                    <button type="button" wire:click="closeDeleteModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center mb-4">
                    <div class="shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Are you sure you want to delete permit {{ $permitToDelete->permit_id ?? '' }}?</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <x-button
                        variant="outline-secondary"
                        size="sm"
                        wire:click="closeDeleteModal"
                        wire:loading.attr="disabled"
                        wire:target="closeDeleteModal"
                    >
                        <span wire:target="closeDeleteModal">Cancel</span>
                    </x-button>
                    <x-button
                        variant="danger"
                        size="sm"
                        wire:click="confirmDeletePermit"
                        wire:loading.attr="disabled"
                        wire:target="confirmDeletePermit"
                    >
                        <span wire:loading.remove wire:target="confirmDeletePermit">Delete Permit</span>
                        <span wire:loading wire:target="confirmDeletePermit">Deleting...</span>
                    </x-button>
                </div>
            </div>
        </div>
    @endif
</div>
