<div>
    @if ($showModal)
        <div class="fixed inset-0 z-9998 flex items-center justify-center p-4" wire:ignore.self>
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>

            <div class="relative w-full max-w-2xl p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $locationName }} Areas</h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 mb-6">
                    @php
                        $isStatusFilterActive = (bool) ($showStatusFilterDropdown || ($statusFilter ?? 'all') !== 'all');
                    @endphp

                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input
                            wire:model.live="search"
                            type="text"
                            placeholder="Search areas..."
                            class="w-full pl-9 pr-12 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                        />

                        <button type="button" wire:click="toggleStatusFilterDropdown" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 transition-colors cursor-pointer {{ $isStatusFilterActive ? 'text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300' }}" title="Status Filter ({{ ucfirst($statusFilter) }})">
                            <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M15 2v1.67l-5 4.759V14H6V8.429l-5-4.76V2h14zM7 8v5h2V8l5-4.76V3H2v.24L7 8z"/>
                            </svg>
                        </button>

                        @if ($showStatusFilterDropdown)
                            <div class="absolute top-full mt-2 w-44 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 left-0 right-0 md:left-auto md:right-0">
                                <div class="p-2 space-y-1">
                                    <button type="button" wire:click="setStatusFilter('all')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">All</button>
                                    <button type="button" wire:click="setStatusFilter('enabled')" class="w-full text-left px-3 py-2 text-sm rounded-md hover:bg-slate-50 dark:hover:bg-gray-700 cursor-pointer {{ $statusFilter === 'enabled' ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200' }}">Enabled</button>
                                    <button type="button" wire:click="setStatusFilter('disabled')" class="w-full text-left px-3 py-2 text-sm rounded-md hover:bg-slate-50 dark:hover:bg-gray-700 cursor-pointer {{ $statusFilter === 'disabled' ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-200' }}">Disabled</button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="overflow-y-auto">
                            <table class="w-full text-left table-auto">
                                <thead class="bg-slate-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200">Name</th>
                                        <th class="px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 text-center">Status</th>
                                        <th class="px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($areas as $area)
                                        <tr class="border-t border-gray-200 dark:border-gray-700">
                                            <td class="px-3 py-2">
                                                @if ($editingAreaId === (int) $area->id)
                                                    <div>
                                                        <input
                                                            wire:model.defer="editingAreaName"
                                                            type="text"
                                                            class="w-full px-2 py-1 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400"
                                                        />
                                                        @error('editingAreaName')
                                                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                @else
                                                    <div class="text-sm text-slate-800 dark:text-slate-200">{{ $area->name }}</div>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                @if ($area->is_disabled)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">Disabled</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Enabled</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex justify-center gap-2">
                                                    @if ($editingAreaId === (int) $area->id)
                                                        <button type="button" wire:click="saveEdit" class="p-1 text-green-600 hover:text-green-800 cursor-pointer" title="Save">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        </button>
                                                        <button type="button" wire:click="cancelEdit" class="p-1 text-gray-500 hover:text-gray-700 cursor-pointer" title="Cancel">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <button type="button" wire:click="startEdit('{{ $area->id }}')" class="p-1 text-blue-600 hover:text-blue-800 cursor-pointer" title="Edit">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </button>
                                                        <button type="button" wire:click="toggleDisable('{{ $area->id }}')" class="p-1 {{ $area->is_disabled ? 'text-green-600 hover:text-green-800' : 'text-red-600 hover:text-red-800' }} cursor-pointer" title="{{ $area->is_disabled ? 'Enable' : 'Disable' }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M12 2a10 10 0 1010 10A10 10 0 0012 2z"></path>
                                                            </svg>
                                                        </button>
                                                        @if (auth()->check() && (int) auth()->user()->user_type === 2)
                                                            <button type="button" wire:click="deleteArea('{{ $area->id }}')" class="p-1 text-red-600 hover:text-red-800 cursor-pointer" title="Delete">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m2 0V5a2 2 0 012-2h2a2 2 0 012 2v2"></path>
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="border-t border-gray-200 dark:border-gray-700">
                                            <td colspan="3" class="px-3 py-8 text-center text-sm text-gray-600 dark:text-gray-400">No areas found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if ($areas->hasPages())
                            <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 bg-white dark:bg-gray-800">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-700 dark:text-gray-300">
                                        Showing {{ $areas->firstItem() }} to {{ $areas->lastItem() }} of {{ $areas->total() }} results
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if ($areas->onFirstPage())
                                            <button type="button" disabled class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                                Previous
                                            </button>
                                        @else
                                            <button type="button" wire:click="previousPage" class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                                Previous
                                            </button>
                                        @endif
                                        
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            Page {{ $areas->currentPage() }} of {{ $areas->lastPage() }}
                                        </span>
                                        
                                        @if ($areas->hasMorePages())
                                            <button type="button" wire:click="nextPage" class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                                Next
                                            </button>
                                        @else
                                            <button type="button" disabled class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed">
                                                Next
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
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
                        <span wire:target="closeModal">Close</span>
                    </x-button>
                    <x-button
                        variant="primary"
                        size="sm"
                        wire:click="openAddAreaModal"
                        wire:loading.attr="disabled"
                        wire:target="openAddAreaModal"
                    >
                        <span wire:target="openAddAreaModal">Add Area</span>
                    </x-button>
                </div>
            </div>
        </div>

        @if ($showDisableConfirmModal)
            <div class="fixed inset-0 z-9998 flex items-center justify-center p-4" wire:ignore.self>
                <div class="fixed inset-0 bg-black/50" wire:click="closeDisableConfirmModal"></div>

                <div class="relative w-full max-w-md p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $confirmAreaIsDisabled ? 'Enable Area' : 'Disable Area' }}</h3>
                        <button type="button" wire:click="closeDisableConfirmModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-center mb-4">
                        <div class="shrink-0 w-12 h-12 {{ $confirmAreaIsDisabled ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} rounded-full flex items-center justify-center">
                            @if($confirmAreaIsDisabled)
                                <svg width="24" height="24" viewBox="0 0 15 15" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600 dark:text-green-300">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11.4669 3.72684C11.7558 3.91574 11.8369 4.30308 11.648 4.59198L7.39799 11.092C7.29783 11.2452 7.13556 11.3467 6.95402 11.3699C6.77247 11.3931 6.58989 11.3355 6.45446 11.2124L3.70446 8.71241C3.44905 8.48022 3.43023 8.08494 3.66242 7.82953C3.89461 7.57412 4.28989 7.55529 4.5453 7.78749L6.75292 9.79441L10.6018 3.90792C10.7907 3.61902 11.178 3.53795 11.4669 3.72684Z"/>
                                </svg>
                            @else
                                <svg width="24" height="24" viewBox="0 0 48 48" version="1" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-6 h-6 text-red-600 dark:text-red-300">
                                    <path d="M24,6C14.1,6,6,14.1,6,24s8.1,18,18,18s18-8.1,18-18S33.9,6,24,6z M24,10c3.1,0,6,1.1,8.4,2.8L12.8,32.4 C11.1,30,10,27.1,10,24C10,16.3,16.3,10,24,10z M24,38c-3.1,0-6-1.1-8.4-2.8l19.6-19.6C36.9,18,38,20.9,38,24C38,31.7,31.7,38,24,38 z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $confirmAreaIsDisabled ? 'Enable area' : 'Disable area' }} <span class="font-semibold">{{ $confirmAreaName }}</span>?
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @if($confirmAreaIsDisabled)
                                    This will allow the area to be selected again.
                                @else
                                    This will hide/disable the area from normal use. Data will be preserved.
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <x-button
                            variant="outline-secondary"
                            size="sm"
                            wire:click="closeDisableConfirmModal"
                            wire:loading.attr="disabled"
                            wire:target="closeDisableConfirmModal"
                        >
                            <span wire:target="closeDisableConfirmModal">Cancel</span>
                        </x-button>
                        <x-button
                            variant="{{ $confirmAreaIsDisabled ? 'success' : 'danger' }}"
                            size="sm"
                            wire:click="confirmDisable"
                            wire:loading.attr="disabled"
                            wire:target="confirmDisable"
                            :loading="$processing"
                        >
                            <span wire:loading.remove wire:target="confirmDisable">
                                {{ $confirmAreaIsDisabled ? 'Enable Area' : 'Disable Area' }}
                            </span>
                            <span wire:loading.inline-flex wire:target="confirmDisable" class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ $confirmAreaIsDisabled ? 'Enabling...' : 'Disabling...' }}
                            </span>
                        </x-button>
                    </div>
                </div>
            </div>
        @endif

        @if ($showDeleteConfirmModal)
            <div class="fixed inset-0 z-9998 flex items-center justify-center p-4" wire:ignore.self>
                <div class="fixed inset-0 bg-black/50" wire:click="closeDeleteConfirmModal"></div>

                <div class="relative w-full max-w-md p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delete Area</h3>
                        <button type="button" wire:click="closeDeleteConfirmModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
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
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Are you sure you want to delete {{ $confirmAreaName }}?</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <x-button
                            variant="outline-secondary"
                            size="sm"
                            wire:click="closeDeleteConfirmModal"
                            wire:loading.attr="disabled"
                            wire:target="closeDeleteConfirmModal"
                        >
                            <span wire:target="closeDeleteConfirmModal">Cancel</span>
                        </x-button>
                        <x-button
                            variant="danger"
                            size="sm"
                            wire:click="confirmDelete"
                            wire:loading.attr="disabled"
                            wire:target="confirmDelete"
                        >
                            <span wire:loading.remove wire:target="confirmDelete">Delete Area</span>
                            <span wire:loading.inline-flex wire:target="confirmDelete" class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Deleting...
                            </span>
                        </x-button>
                    </div>
                </div>
            </div>
        @endif

        @if ($showAddAreaModal)
            <div class="fixed inset-0 z-9998 flex items-center justify-center p-4" wire:ignore.self>
                <div class="fixed inset-0 bg-black/50" wire:click="closeAddAreaModal"></div>

                <div class="relative w-full max-w-lg p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add New Area</h3>
                        <button type="button" wire:click="closeAddAreaModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="createArea">
                        <div class="mb-6">
                            <input
                                wire:model.defer="newAreaName"
                                type="text"
                                placeholder="Enter new area name"
                                class="w-full px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 dark:focus:ring-orange-400"
                            />
                            @error('newAreaName')
                                <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <x-button
                                variant="outline-secondary"
                                size="sm"
                                wire:click="closeAddAreaModal"
                                wire:loading.attr="disabled"
                                wire:target="closeAddAreaModal"
                            >
                                <span wire:target="closeAddAreaModal">Cancel</span>
                            </x-button>
                            <x-button
                                variant="primary"
                                size="sm"
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="createArea"
                            >
                                <span wire:loading.remove wire:target="createArea">Create</span>
                                <span wire:loading.inline-flex wire:target="createArea" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Creating...
                                </span>
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endif
</div>
