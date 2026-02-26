<x-layout>
    <nav class="shadow-lg border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 sticky top-0 z-30">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">FVPortal</h1>
                </div>

                <div class="flex items-center space-x-4">
                    <x-dark-mode-toggle />

                    <a href="{{ route('login.admin') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <livewire:guest.location-landing />
</x-layout>
