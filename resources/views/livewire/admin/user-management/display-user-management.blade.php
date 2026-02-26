<div>
    <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between md:gap-6">
        <div class="text-center md:text-left">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Users Management</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage your users here</p>
        </div>
        <div class="flex flex-col gap-3 md:flex-row md:gap-3 md:items-center">
            <div class="flex flex-row gap-3 items-center w-full md:w-auto">
                <div class="relative shrink-0 flex-1 md:flex-initial">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input
                        wire:model.live="search"
                        placeholder="Search users..."
                        class="w-full pl-11 pr-12 py-3 text-sm bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition-all placeholder:text-gray-400 dark:placeholder:text-gray-500 shadow-sm dark:shadow-md"
                    />
                    <button type="button" wire:click="toggleFilterDropdown" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors cursor-pointer">
                        <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="#9CA3AF" class="w-5 h-5">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M15 2v1.67l-5 4.759V14H6V8.429l-5-4.76V2h14zM7 8v5h2V8l5-4.76V3H2v.24L7 8z"/>
                        </svg>
                    </button>

                    @if ($showFilterDropdown)
                        <div class="absolute top-full mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg dark:shadow-xl border border-gray-200 dark:border-gray-700 z-50 left-0 right-0 md:left-auto md:right-0 md:w-80">
                            <div class="p-4">
                                <div class="grid grid-cols-2 gap-1">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Status</h3>
                                        <div class="space-y-2">
                                            <label class="flex items-center">
                                                <input type="radio" wire:model="statusFilter" value="all" class="mr-2">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">All Users</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" wire:model="statusFilter" value="disabled" class="mr-2">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">Disabled</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" wire:model="statusFilter" value="enabled" class="mr-2">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">Enabled</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Date Range</h3>
                                        <div class="space-y-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                                                <input
                                                    type="date"
                                                    wire:model="dateFrom"
                                                    class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                                    placeholder="YYYY-MM-DD"
                                                    max="{{ $dateTo ?: now()->format('Y-m-d') }}"
                                                    wire:target="dateFrom"
                                                    wire:loading.attr="disabled"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                                                <input
                                                    type="date"
                                                    wire:model="dateTo"
                                                    class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                                    placeholder="YYYY-MM-DD"
                                                    max="{{ now()->format('Y-m-d') }}"
                                                    min="{{ $dateFrom ?: '' }}"
                                                    wire:target="dateTo"
                                                    wire:loading.attr="disabled"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-between mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <button type="button" wire:click="resetFilters" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 cursor-pointer">Reset</button>
                                    <button type="button" wire:click="toggleFilterDropdown" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 cursor-pointer">Done</button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <button type="button" wire:click="$dispatch('openCreateModal')" class="inline-flex items-center justify-center px-4 py-3 text-sm font-medium text-white bg-orange-600 border border-orange-600 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-150 whitespace-nowrap shrink-0 md:px-4 cursor-pointer">
                    <span class="hidden md:inline">Add User</span>
                    <span class="md:hidden">Add</span>
                </button>
            </div>
        </div>
    </div>

    <div wire:poll.30s class="relative flex flex-col w-full h-full text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 shadow-md dark:shadow-lg rounded-lg bg-clip-border">
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left table-auto min-w-max">
                <thead>
                    <tr>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 cursor-pointer hover:bg-slate-100 dark:hover:bg-gray-600" wire:click="sortBy('first_name')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200 flex items-center gap-1">
                                First Name
                                @if ($sortField === 'first_name')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 cursor-pointer hover:bg-slate-100 dark:hover:bg-gray-600" wire:click="sortBy('last_name')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200 flex items-center gap-1">
                                Last Name
                                @if ($sortField === 'last_name')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 cursor-pointer hover:bg-slate-100 dark:hover:bg-gray-600" wire:click="sortBy('username')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200 flex items-center gap-1">
                                Username
                                @if ($sortField === 'username')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 cursor-pointer hover:bg-slate-100 dark:hover:bg-gray-600" wire:click="sortBy('created_at')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200 flex items-center gap-1">
                                Created Date
                                @if ($sortField === 'created_at')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sortDirection === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 text-center">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">Status</p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 text-center">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">Actions</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="even:bg-slate-50 dark:even:bg-gray-700/50 hover:bg-slate-100 dark:hover:bg-gray-700">
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ $user->first_name }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ $user->last_name }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ $user->username }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ $user->created_at ? $user->created_at->format('d M, Y') : 'N/A' }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                @if($user->is_disabled)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">Disabled</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Enabled</span>
                                @endif
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5">
                                <div class="flex gap-1 md:gap-2 justify-center">
                                    <button wire:click="$dispatch('openEditModal', '{{ $user->id }}')" class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors cursor-pointer">Edit</button>
                                    <button wire:click="$dispatch('openResetPasswordModal', '{{ $user->id }}')" class="px-3 py-1 text-xs font-medium text-yellow-600 bg-yellow-50 rounded-md hover:bg-yellow-100 transition-colors cursor-pointer">Reset Password</button>
                                    <button wire:click="$dispatch('openDisableModal', '{{ $user->id }}')" class="px-3 py-1 text-xs font-medium {{ $user->is_disabled ? 'text-green-600 bg-green-50 hover:bg-green-100' : 'text-red-600 bg-red-50 hover:bg-red-100' }} rounded-md transition-colors cursor-pointer">{{ $user->is_disabled ? 'Enable' : 'Disable' }}</button>
                                    <button wire:click="$dispatch('openDeleteModal', '{{ $user->id }}')" class="px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 transition-colors cursor-pointer">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">No users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-4 p-4">
            @forelse ($users as $user)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm dark:shadow-md p-4 space-y-3">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->first_name }} {{ $user->last_name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->username }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->created_at ? $user->created_at->format('d M, Y') : 'N/A' }}</p>
                        </div>
                        <div class="text-center">
                            @if($user->is_disabled)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">Disabled</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Enabled</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button wire:click="$dispatch('openEditModal', '{{ $user->id }}')" class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors">Edit</button>
                        <button wire:click="$dispatch('openResetPasswordModal', '{{ $user->id }}')" class="px-3 py-1 text-xs font-medium text-yellow-600 bg-yellow-50 rounded-md hover:bg-yellow-100 transition-colors">Reset Password</button>
                        <button wire:click="$dispatch('openDisableModal', '{{ $user->id }}')" class="px-3 py-1 text-xs font-medium {{ $user->is_disabled ? 'text-green-600 bg-green-50 hover:bg-green-100' : 'text-red-600 bg-red-50 hover:bg-red-100' }} rounded-md transition-colors">{{ $user->is_disabled ? 'Enable' : 'Disable' }}</button>
                        <button wire:click="$dispatch('openDeleteModal', '{{ $user->id }}')" class="px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 transition-colors">Delete</button>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center py-12">No users found</div>
            @endforelse
        </div>

        @if ($users->hasPages())
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center px-3 md:px-4 py-3 border-t border-slate-200 dark:border-gray-700 gap-3 sm:gap-0">
                <div class="text-xs md:text-sm text-slate-500 dark:text-slate-400 text-center sm:text-left">
                    Showing <b>{{ $users->firstItem() }}-{{ $users->lastItem() }}</b> of {{ $users->total() }}
                </div>
                <x-custom-pagination
                    :current-page="$currentPage"
                    :last-page="$lastPage"
                    :pages="$pages"
                    on-page-change="gotoPage"
                />
            </div>
        @endif
    </div>

    <livewire:admin.user-management.create />
    <livewire:admin.user-management.edit />
    <livewire:admin.user-management.delete />
    <livewire:admin.user-management.disable />
    <livewire:admin.user-management.reset-password />
</div>
