<div>
    <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-2.5 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21,20a2,2,0,0,1-2,2H5a2,2,0,0,1-2-2,6,6,0,0,1,6-6h6A6,6,0,0,1,21,20Zm-9-8A5,5,0,1,0,7,7,5,5,0,0,0,12,12Z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">My Permits</h1>
                    </div>
                </div>
                
                <!-- Search Bar -->
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live="search"
                        placeholder="Search permit ID..." 
                        class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent"
                    />
                </div>
            </div>

            @if ($permits->count() > 0)
                <!-- Permit Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($permits as $permit)
                        <div wire:key="my-{{ $permit->id }}" 
                             class="group bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-green-300 dark:hover:border-green-600 hover:shadow-sm transition-all duration-200 cursor-pointer"
                             wire:click="viewPermit({{ $permit->id }})">
                            
                            <div class="p-5">
                                <!-- Header with Permit ID and Status -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-{{ $this->getStatusColor($permit->status) }}-100 dark:bg-{{ $this->getStatusColor($permit->status) }}-900/30 rounded-lg">
                                            <svg class="h-4 w-4 text-{{ $this->getStatusColor($permit->status) }}-600 dark:text-{{ $this->getStatusColor($permit->status) }}-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $permit->permit_id }}</h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Duration: {{ gmdate('H:i', $permit->expected_duration_seconds) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center text-blue-600 dark:text-blue-400 text-sm font-medium cursor-pointer">
                                        <span class="mr-1">View Details</span>
                                        <svg class="h-4 w-4 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Visitor Information -->
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Visitor Name</p>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $permit->names }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $permits->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <div class="mx-auto w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-2">No permits received</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">You haven't received any permits yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
