<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @endif

    <div class="p-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-2.5 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M12,2 C17.5228475,2 22,6.4771525 22,12 C22,17.5228475 17.5228475,22 12,22 C6.4771525,22 2,17.5228475 2,12 C2,6.4771525 6.4771525,2 12,2 Z M12,4 C7.581722,4 4,7.581722 4,12 C4,16.418278 7.581722,20 12,20 C16.418278,20 20,16.418278 20,12 C20,7.581722 16.418278,4 12,4 Z M12,6 C12.5128358,6 12.9355072,6.38604019 12.9932723,6.88337887 L13,7 L13,11.5857864 L14.7071068,13.2928932 C15.0976311,13.6834175 15.0976311,14.3165825 14.7071068,14.7071068 C14.3466228,15.0675907 13.7793918,15.0953203 13.3871006,14.7902954 L13.2928932,14.7071068 L11.2928932,12.7071068 C11.1366129,12.5508265 11.0374017,12.3481451 11.0086724,12.131444 L11,12 L11,7 C11,6.44771525 11.4477153,6 12,6 Z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Scheduled Permits</h1>
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
                        class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent"
                    />
                </div>
            </div>

            @if ($permits->count() > 0)
                <!-- Permit Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($permits as $permit)
                        <div wire:key="scheduled-{{ $permit->id }}" 
                             class="group bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-purple-300 dark:hover:border-purple-600 hover:shadow-sm transition-all duration-200 cursor-pointer"
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
                    <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-2">No scheduled permits</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">There are currently no scheduled permits for your location.</p>
                </div>
            @endif
        </div>
    </div>
</div>
