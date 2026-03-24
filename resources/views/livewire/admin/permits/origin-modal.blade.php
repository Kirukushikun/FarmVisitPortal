<div>
    @if ($showOriginModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <div class="fixed inset-0 bg-black/50" wire:click="closeOriginModal"></div>

            <div class="relative w-full max-w-md p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Create Permit</h3>
                    <button type="button" wire:click="closeOriginModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">How are the visitors coming in?</p>

                <div class="grid grid-cols-2 gap-4">
                    <button
                        type="button"
                        wire:click="selectOriginMode('single')"
                        class="flex flex-col items-center justify-center gap-3 p-5 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all cursor-pointer group"
                    >
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition-colors">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="text-center">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">Single Origin</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">All visitors from same location</div>
                        </div>
                    </button>

                    <button
                        type="button"
                        wire:click="selectOriginMode('multiple')"
                        class="flex flex-col items-center justify-center gap-3 p-5 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-orange-500 dark:hover:border-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-all cursor-pointer group"
                    >
                        <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-full group-hover:bg-orange-200 dark:group-hover:bg-orange-900/50 transition-colors">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="text-center">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">Multiple Origins</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Visitors from different locations</div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>