@props(['subtitle' => ''])

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ $slot }}
    </h1>

    @if($subtitle)
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ $subtitle }}
        </p>
    @endif
</div>
