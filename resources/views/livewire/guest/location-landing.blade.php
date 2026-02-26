<div class="min-h-screen">
    <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-30">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Choose a Location</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Select your farm/location to continue to login</p>
                </div>

                <div class="w-full md:w-96">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            wire:model.live="search"
                            type="text"
                            placeholder="Search locations..."
                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 pl-10 pr-4 py-3 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($locations as $location)
                <a
                    href="{{ route('login.user', ['location' => $location->id]) }}"
                    class="group rounded-2xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5 hover:shadow-md transition"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                                {{ $location->name }}
                            </h2>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Click to continue</p>
                        </div>
                        <div class="shrink-0 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 px-3 py-1 text-xs font-medium">
                            {{ (int) ($location->destination_permits_count ?? 0) }} Permits
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full">
                    <div class="rounded-2xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-8 text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">No locations found.</p>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($locations->hasPages())
            <div class="mt-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <div class="text-xs md:text-sm text-slate-500 dark:text-slate-400 text-center sm:text-left">
                    Showing <b>{{ $locations->firstItem() }}-{{ $locations->lastItem() }}</b> of {{ $locations->total() }}
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
