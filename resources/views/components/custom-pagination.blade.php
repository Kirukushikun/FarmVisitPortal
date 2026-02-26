@props([
    'currentPage' => 1,
    'lastPage' => 1,
    'pages' => [],
    'onPageChange' => null
])

<div class="flex space-x-1 justify-center sm:justify-end overflow-x-auto pb-2 sm:pb-0">
    @if ($currentPage == 1)
        <button type="button" class="px-2 md:px-3 py-1 min-w-9 min-h-9 text-xs md:text-sm font-normal text-slate-500 dark:text-slate-400 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-600 rounded cursor-not-allowed opacity-40 shadow-sm" disabled>
            Prev
        </button>
    @else
        <button type="button" wire:click="{{ $onPageChange ?? 'gotoPage' }}({{ $currentPage - 1 }})" class="px-2 md:px-3 py-1 min-w-9 min-h-9 text-xs md:text-sm font-normal text-slate-500 dark:text-slate-400 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-600 rounded hover:bg-slate-50 dark:hover:bg-gray-700 hover:border-slate-400 dark:hover:border-gray-500 transition duration-200 ease shadow-md hover:shadow-lg cursor-pointer">
            Prev
        </button>
    @endif

    @foreach ($pages as $page)
        @if ($page == $currentPage)
            <button type="button" class="px-2 md:px-3 py-1 min-w-9 min-h-9 text-xs md:text-sm font-normal text-white dark:text-white bg-slate-800 dark:bg-slate-700 border border-slate-800 dark:border-slate-600 rounded hover:bg-slate-600 dark:hover:bg-slate-600 hover:border-slate-600 dark:hover:border-slate-500 transition duration-200 ease">
                {{ $page }}
            </button>
        @else
            <button type="button" wire:click="{{ $onPageChange ?? 'gotoPage' }}({{ $page }})" class="px-2 md:px-3 py-1 min-w-9 min-h-9 text-xs md:text-sm font-normal text-slate-500 dark:text-slate-400 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-600 rounded hover:bg-slate-50 dark:hover:bg-gray-700 hover:border-slate-400 dark:hover:border-gray-500 transition duration-200 ease cursor-pointer">
                {{ $page }}
            </button>
        @endif
    @endforeach

    @if ($currentPage < $lastPage)
        <button type="button" wire:click="{{ $onPageChange ?? 'gotoPage' }}({{ $currentPage + 1 }})" class="px-2 md:px-3 py-1 min-w-9 min-h-9 text-xs md:text-sm font-normal text-slate-500 dark:text-slate-400 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-600 rounded hover:bg-slate-50 dark:hover:bg-gray-700 hover:border-slate-400 dark:hover:border-gray-500 transition duration-200 ease shadow-md hover:shadow-lg cursor-pointer">
            Next
        </button>
    @else
        <button type="button" class="px-2 md:px-3 py-1 min-w-9 min-h-9 text-xs md:text-sm font-normal text-slate-500 dark:text-slate-400 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-600 rounded cursor-not-allowed opacity-40 shadow-sm" disabled>
            Next
        </button>
    @endif
</div>
