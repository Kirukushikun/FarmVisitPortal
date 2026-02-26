<div>
    @if ($showModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>

            <div class="relative w-full max-w-md p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $isCurrentlyDisabled ? 'Enable Account' : 'Disable Account' }}</h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center mb-4">
                    <div class="shrink-0 w-12 h-12 {{ $isCurrentlyDisabled ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center">
                        @if($isCurrentlyDisabled)
                            <svg width="24" height="24" viewBox="0 0 15 15" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600 dark:text-white">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.4669 3.72684C11.7558 3.91574 11.8369 4.30308 11.648 4.59198L7.39799 11.092C7.29783 11.2452 7.13556 11.3467 6.95402 11.3699C6.77247 11.3931 6.58989 11.3355 6.45446 11.2124L3.70446 8.71241C3.44905 8.48022 3.43023 8.08494 3.66242 7.82953C3.89461 7.57412 4.28989 7.55529 4.5453 7.78749L6.75292 9.79441L10.6018 3.90792C10.7907 3.61902 11.178 3.53795 11.4669 3.72684Z"/>
                            </svg>
                        @else
                            <svg width="24" height="24" viewBox="0 0 48 48" version="1" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-6 h-6 text-red-600 dark:text-white">
                                <path d="M24,6C14.1,6,6,14.1,6,24s8.1,18,18,18s18-8.1,18-18S33.9,6,24,6z M24,10c3.1,0,6,1.1,8.4,2.8L12.8,32.4 C11.1,30,10,27.1,10,24C10,16.3,16.3,10,24,10z M24,38c-3.1,0-6-1.1-8.4-2.8l19.6-19.6C36.9,18,38,20.9,38,24C38,31.7,31.7,38,24,38 z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $isCurrentlyDisabled ? 'Enable account for' : 'Disable account for' }} <span class="font-semibold">{{ $userName }}</span>?
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($isCurrentlyDisabled)
                                This will allow the user to access the system again.
                            @else
                                This will prevent the user from accessing the system. Their data will be preserved.
                            @endif
                        </p>
                    </div>
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
                        variant="{{ $isCurrentlyDisabled ? 'success' : 'danger' }}"
                        size="sm"
                        wire:click="toggleDisable"
                        wire:loading.attr="disabled"
                        wire:target="toggleDisable"
                        :loading="$processing"
                    >
                        <span wire:loading.remove wire:target="toggleDisable">
                            {{ $isCurrentlyDisabled ? 'Enable Account' : 'Disable Account' }}
                        </span>
                        <span wire:loading wire:target="toggleDisable">
                            {{ $isCurrentlyDisabled ? 'Enabling...' : 'Disabling...' }}
                        </span>
                    </x-button>
                </div>
            </div>
        </div>
    @endif
</div>
