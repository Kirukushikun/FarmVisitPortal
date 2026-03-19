<div>
@php
    function permitStatusPill(int $status): string {
        switch ($status) {
            case 0:
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #fef3c7; color: #92400e;">Scheduled</span>';
            case 1:
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #dbeafe; color: #1e40af;">In Progress</span>';
            case 2:
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #dcfce7; color: #166534;">Completed</span>';
            case 3:
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #fecaca; color: #991b1b;">Cancelled</span>';
            case 4:
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #fed7aa; color: #9a3412;">On Hold</span>';
            case 5:
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #ede9fe; color: #5b21b6;">Returned</span>';
            default:
                return '<span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #f3f4f6; color: #374151;">Unknown</span>';
        }
    }

    function permitDuration($hours): string {
        if ($hours === null) {
            return 'N/A';
        }

        $hoursFloat = max(0, (float) $hours);
        $totalMinutes = (int) round($hoursFloat * 60);

        $displayHours = intdiv($totalMinutes, 60);
        $displayMinutes = $totalMinutes % 60;

        if ($displayMinutes === 0) {
            return $displayHours . 'h';
        }

        return $displayHours . 'h ' . $displayMinutes . 'm';
    }

    $isFilterActive = (bool) ($showFilterDropdown
        || trim((string) ($status ?? '')) !== ''
        || trim((string) ($dateFrom ?? '')) !== ''
        || trim((string) ($dateTo ?? '')) !== ''
        || trim((string) ($completedDateFrom ?? '')) !== ''
        || trim((string) ($completedDateTo ?? '')) !== ''
        || trim((string) ($visitDateFrom ?? '')) !== ''
        || trim((string) ($visitDateTo ?? '')) !== '');
@endphp

<div>
    <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between md:gap-6">
        <div class="text-center md:text-left">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Permits</h1>
            <p class="text-gray-600 dark:text-gray-400">All submitted permits</p>
        </div>
        <div class="flex flex-col gap-3 md:flex-row md:gap-3 md:items-center">
            <div class="flex flex-row gap-3 items-center w-full md:w-auto">
                <div class="relative shrink-0 flex-1 md:flex-initial">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input
                        wire:model.live="search"
                        placeholder="Search permits..."
                        class="w-full pl-11 pr-12 py-3 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition-all placeholder:text-gray-400 dark:placeholder:text-gray-500 shadow-sm dark:shadow-md"
                    />
                    <button type="button" wire:click="toggleFilterDropdown" class="absolute right-2 top-1/2 -translate-y-1/2 p-2 transition-colors cursor-pointer {{ $isFilterActive ? 'text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300' }}">
                        <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M15 2v1.67l-5 4.759V14H6V8.429l-5-4.76V2h14zM7 8v5h2V8l5-4.76V3H2v.24L7 8z"/>
                        </svg>
                    </button>

                    @if ($showFilterDropdown)
                        <div class="absolute top-full mt-2 w-2xl bg-white dark:bg-gray-800 rounded-lg shadow-lg dark:shadow-xl border border-gray-200 dark:border-gray-700 z-50 left-0 right-0 md:left-auto md:right-0">
                            <div class="p-4">
                                <div class="grid grid-cols-4 gap-2">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Created Date</h3>
                                        <div class="space-y-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                                                <input
                                                    type="date"
                                                    wire:model.defer="pendingDateFrom"
                                                    class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                                    placeholder="YYYY-MM-DD"
                                                    max="{{ $pendingDateTo ?: now()->format('Y-m-d') }}"
                                                    wire:target="pendingDateFrom"
                                                    wire:loading.attr="disabled"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                                                <input
                                                    type="date"
                                                    wire:model.defer="pendingDateTo"
                                                    class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                                    placeholder="YYYY-MM-DD"
                                                    max="{{ now()->format('Y-m-d') }}"
                                                    min="{{ $pendingDateFrom ?: '' }}"
                                                    wire:target="pendingDateTo"
                                                    wire:loading.attr="disabled"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Visit Date</h3>
                                        <div class="space-y-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                                                <input
                                                    type="date"
                                                    wire:model.defer="visitDateFrom"
                                                    class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                                    placeholder="YYYY-MM-DD"
                                                    max="{{ $visitDateTo ?: now()->format('Y-m-d') }}"
                                                    wire:target="visitDateFrom"
                                                    wire:loading.attr="disabled"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                                                <input
                                                    type="date"
                                                    wire:model.defer="visitDateTo"
                                                    class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                                    placeholder="YYYY-MM-DD"
                                                    max="{{ now()->format('Y-m-d') }}"
                                                    min="{{ $visitDateFrom ?: '' }}"
                                                    wire:target="visitDateTo"
                                                    wire:loading.attr="disabled"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Completed Date</h3>
                                        <div class="space-y-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                                                <input
                                                    type="date"
                                                    wire:model.defer="completedDateFrom"
                                                    class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                                    placeholder="YYYY-MM-DD"
                                                    max="{{ $completedDateTo ?: now()->format('Y-m-d') }}"
                                                    wire:target="completedDateFrom"
                                                    wire:loading.attr="disabled"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                                                <input
                                                    type="date"
                                                    wire:model.defer="completedDateTo"
                                                    class="w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                                    placeholder="YYYY-MM-DD"
                                                    max="{{ now()->format('Y-m-d') }}"
                                                    min="{{ $completedDateFrom ?: '' }}"
                                                    wire:target="completedDateTo"
                                                    wire:loading.attr="disabled"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Status</h3>
                                        <div class="space-y-2">
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model.defer="pendingStatusFilter" value="0" class="mr-2 cursor-pointer">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">Scheduled</span>
                                            </label>
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model.defer="pendingStatusFilter" value="1" class="mr-2 cursor-pointer">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">In Progress</span>
                                            </label>
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model.defer="pendingStatusFilter" value="2" class="mr-2 cursor-pointer">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">Completed</span>
                                            </label>
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model.defer="pendingStatusFilter" value="3" class="mr-2 cursor-pointer">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">Cancelled</span>
                                            </label>
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model.defer="pendingStatusFilter" value="4" class="mr-2 cursor-pointer">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">On Hold</span>
                                            </label>
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model.defer="pendingStatusFilter" value="5" class="mr-2 cursor-pointer">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">Returned</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-between mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <button type="button" wire:click="resetFilters" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 cursor-pointer">Reset</button>
                                    <button type="button" wire:click="applyFilters" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 cursor-pointer">Done</button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <a
                    href="{{ route('admin.permits.create', ['return' => $returnUrl]) }}"
                    class="inline-flex items-center justify-center px-4 py-3 text-sm font-medium text-white bg-orange-600 border border-orange-600 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all duration-150 whitespace-nowrap shrink-0 md:px-4 cursor-pointer"
                >
                    <span class="hidden md:inline">Create Permit</span>
                    <span class="md:hidden">Create</span>
                </a>
            </div>
        </div>
    </div>

    <div wire:poll.30s class="relative flex flex-col w-full h-full text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 shadow-md dark:shadow-lg rounded-lg bg-clip-border">
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left table-auto min-w-max">
                <thead>
                    <tr>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 cursor-pointer hover:bg-slate-100 dark:hover:bg-gray-600" wire:click="sortBy('permit_id')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200 flex items-center gap-1">
                                Permit ID
                                @if ($sortField === 'permit_id')
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
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 cursor-pointer hover:bg-slate-100 dark:hover:bg-gray-600" wire:click="sortBy('date_of_visit')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200 flex items-center gap-1">
                                Visit Date / Farm
                                @if ($sortField === 'date_of_visit')
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
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 text-left">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">Created By</p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 text-left">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">Duration</p>
                        </th>
                        <th class="p-3 md:p-4 border-b border-slate-300 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 text-center cursor-pointer hover:bg-slate-100 dark:hover:bg-gray-600" wire:click="sortBy('status')">
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200 flex items-center justify-center gap-1">
                                Status
                                @if ($sortField === 'status')
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
                            <p class="text-xs md:text-sm font-semibold leading-none text-slate-700 dark:text-slate-200">Actions</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permits as $permit)
                        <tr class="even:bg-slate-50 dark:even:bg-gray-700/50 hover:bg-slate-100 dark:hover:bg-gray-700">
                            <td class="p-3 md:p-4 py-4 md:py-5 text-left">
                                <div class="space-y-1">
                                    <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200 font-medium">{{ $permit->permit_id }}</p>
                                    <p class="block text-[11px] md:text-xs text-slate-500 dark:text-slate-400">{{ $permit->created_at ? $permit->created_at->format('d M, Y g:i A') : 'N/A' }}</p>
                                </div>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-left">
                                <div class="space-y-1">
                                    <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ $permit->date_of_visit ? $permit->date_of_visit->format('d M, Y') : 'N/A' }}</p>
                                    <p class="block text-[11px] md:text-xs text-slate-500 dark:text-slate-400">{{ $permit->farmLocation?->name ?? 'N/A' }}</p>
                                </div>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-left">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">
                                    {{ trim(($permit->createdBy->first_name ?? '') . ' ' . ($permit->createdBy->last_name ?? '')) ?: ($permit->createdBy->username ?? 'N/A') }}
                                </p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-left">
                                <p class="block text-xs md:text-sm text-slate-800 dark:text-slate-200">{{ permitDuration($permit->expected_duration_hours) }}</p>
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                {!! permitStatusPill((int) $permit->status) !!}
                                @if(((int) $permit->status) === 2 && $permit->receivedBy)
                                    <p class="mt-1 text-[11px] md:text-xs text-slate-500 dark:text-slate-400">
                                        Received by:
                                        {{ trim(($permit->receivedBy->first_name ?? '') . ' ' . ($permit->receivedBy->last_name ?? '')) ?: ($permit->receivedBy->username ?? '') }}
                                    </p>
                                @endif
                            </td>
                            <td class="p-3 md:p-4 py-4 md:py-5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a
                                        href="{{ route('admin.permits.show', $permit) }}"
                                        class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors"
                                        title="View Details"
                                    >
                                        View
                                    </a>
                                    @if (((int) ($permit->status ?? 0)) === 3)
                                        <button
                                            type="button"
                                            wire:click="reschedulePermit({{ $permit->id }})"
                                            class="px-3 py-1 text-xs font-medium text-purple-600 bg-purple-50 rounded-md hover:bg-purple-100 transition-colors cursor-pointer"
                                            title="Reschedule Permit"
                                        >
                                            Reschedule
                                        </button>
                                    @endif
                                    <a
                                        href="{{ route('admin.permits.edit', ['permit' => $permit, 'return' => $returnUrl]) }}"
                                        class="px-3 py-1 text-xs font-medium text-orange-600 bg-orange-50 rounded-md hover:bg-orange-100 transition-colors"
                                        title="Edit Permit"
                                    >
                                        Edit
                                    </a>
                                    @if (auth()->check() && (int) auth()->user()->user_type === 2)
                                        <button
                                            type="button"
                                            wire:click="deletePermit({{ $permit->id }})"
                                            class="px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 transition-colors cursor-pointer"
                                            title="Delete Permit"
                                        >
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-sm text-slate-600 dark:text-gray-400 font-medium">No permits found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden">
            @forelse($permits as $permit)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm dark:shadow-lg p-4 space-y-3 mb-4">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $permit->created_at ? $permit->created_at->format('d M, Y g:i A') : 'N/A' }}</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $permit->permit_id }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Created By: {{ trim(($permit->createdBy->first_name ?? '') . ' ' . ($permit->createdBy->last_name ?? '')) ?: ($permit->createdBy->username ?? 'N/A') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Arrival: {{ $permit->date_of_visit ? $permit->date_of_visit->format('d M, Y') : 'N/A' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Farm: {{ $permit->farmLocation?->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Duration: {{ permitDuration($permit->expected_duration_hours) }}</p>
                        </div>
                        <div class="text-center">
                            {!! permitStatusPill((int) $permit->status) !!}
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <a
                            href="{{ route('admin.permits.show', $permit) }}"
                            class="px-3 py-1 text-xs font-medium text-blue-600 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/50 rounded-md hover:bg-blue-100 dark:hover:bg-blue-900/70 transition-colors"
                            title="View Details">
                            View
                        </a>
                        @if (((int) ($permit->status ?? 0)) === 3)
                            <button
                                type="button"
                                wire:click="reschedulePermit({{ $permit->id }})"
                                class="px-3 py-1 text-xs font-medium text-purple-600 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/50 rounded-md hover:bg-purple-100 dark:hover:bg-purple-900/70 transition-colors cursor-pointer"
                            >
                                Reschedule
                            </button>
                        @endif
                        <a
                            href="{{ route('admin.permits.edit', ['permit' => $permit, 'return' => $returnUrl]) }}"
                            class="px-3 py-1 text-xs font-medium text-orange-600 dark:text-orange-300 bg-orange-50 dark:bg-orange-900/50 rounded-md hover:bg-orange-100 dark:hover:bg-orange-900/70 transition-colors"
                            title="Edit Permit">
                            Edit
                        </a>
                        @if (auth()->check() && (int) auth()->user()->user_type === 2)
                            <button
                                type="button"
                                wire:click="deletePermit({{ $permit->id }})"
                                class="px-3 py-1 text-xs font-medium text-red-600 dark:text-red-300 bg-red-50 dark:bg-red-900/40 rounded-md hover:bg-red-100 dark:hover:bg-red-900/60 transition-colors cursor-pointer"
                            >
                                Delete
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center py-12">
                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No permits found</h3>
                </div>
            @endforelse
        </div>

        @if (is_object($permits) && method_exists($permits, 'hasPages') && $permits->hasPages())
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center px-3 md:px-4 py-3 border-t border-slate-200 dark:border-gray-700 gap-3 sm:gap-0">
                <div class="text-xs md:text-sm text-slate-500 dark:text-slate-400 text-center sm:text-left">
                    Showing <b>{{ $permits->firstItem() }}-{{ $permits->lastItem() }}</b> of {{ $permits->total() }}
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
</div>

@include('livewire.admin.permits.delete-permit')
@include('livewire.admin.permits.reschedule-permit')
</div>
