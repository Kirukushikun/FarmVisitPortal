<div>
    @if ($showModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>

            <div class="relative w-full max-w-md p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Reset Password</h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center mb-4">
                    <div class="shrink-0 w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="#FFB636" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                            <path d="M456.943 48.057c-64.075-64.075-167.962-64.075-232.038 0c-50.857 50.857-61.341 126.792-31.466 187.997l-45.691 45.691a3.594 3.594 0 0 0-1.052 2.59l.403 28.553a3.59 3.59 0 0 1-1.052 2.59l-23.879 23.879a3.591 3.591 0 0 1-3.249.981l-22.888-4.61a3.594 3.594 0 0 0-3.249.981L67.443 362.05a3.59 3.59 0 0 0-1.047 2.357l-1.458 28.668a3.592 3.592 0 0 1-3.881 3.397l-24.246-1.996a3.59 3.59 0 0 0-2.834 1.04l-22.05 22.05a3.59 3.59 0 0 0 0 5.079l10.938 10.937a3.59 3.59 0 0 1 0 5.079L14.526 447a3.595 3.595 0 0 0-1.051 2.456l-1.146 49.15a3.591 3.591 0 0 0 1.051 2.623l7.793 7.793c.72.72 1.711 1.1 2.727 1.047l47.721-2.49a3.586 3.586 0 0 0 2.352-1.047l194.973-194.973c61.205 29.875 137.14 19.391 187.997-31.466c64.076-64.074 64.076-167.961 0-232.036zm-23.812 76.438c-14.532 14.532-38.094 14.532-52.626 0s-14.532-38.094 0-52.626c14.532-14.532 38.094-14.532 52.626 0c14.532 14.532 14.532 38.094 0 52.626z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Are you sure you want to reset {{ $userName }}'s password?</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">This will reset the password to the default password.</p>
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
                        variant="warning"
                        size="sm"
                        wire:click="resetPassword"
                        wire:loading.attr="disabled"
                        wire:target="resetPassword"
                    >
                        <span wire:loading.remove wire:target="resetPassword">Reset Password</span>
                        <span wire:loading wire:target="resetPassword">Resetting...</span>
                    </x-button>
                </div>
            </div>
        </div>
    @endif
</div>
