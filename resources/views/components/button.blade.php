@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconPosition' => 'left',
    'type' => 'button',
    'disabled' => false,
    'fullWidth' => false,
    'loading' => false
])

@php
    $variantKey = is_string($variant) ? $variant : (string) $variant;
    $sizeKey = is_string($size) ? $size : (string) $size;

    $variantClasses = [
        'primary' => 'bg-orange-600 hover:bg-orange-700 text-white border-orange-600 dark:bg-orange-700 dark:hover:bg-orange-800 dark:border-orange-700',
        'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-800 dark:border-gray-700',
        'success' => 'bg-green-600 hover:bg-green-700 text-white border-green-600 dark:bg-green-700 dark:hover:bg-green-800 dark:border-green-700',
        'warning' => 'bg-yellow-500 hover:bg-yellow-600 text-white border-yellow-500 dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:border-yellow-600',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white border-red-600 dark:bg-red-700 dark:hover:bg-red-800 dark:border-red-700',
        'outline-primary' => 'bg-white hover:bg-orange-50 text-orange-600 border-orange-600 dark:bg-gray-800 dark:hover:bg-orange-900/20 dark:text-orange-400 dark:border-orange-500',
        'outline-secondary' => 'bg-white hover:bg-gray-50 text-gray-700 border-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
        'outline-danger' => 'bg-white hover:bg-red-50 text-red-600 border-red-600 dark:bg-gray-800 dark:hover:bg-red-900/20 dark:text-red-400 dark:border-red-500',
        'ghost' => 'bg-transparent hover:bg-gray-100 text-gray-700 border-transparent dark:bg-transparent dark:hover:bg-gray-700 dark:text-gray-300 dark:border-transparent',
        'link' => 'bg-transparent hover:underline text-blue-600 border-transparent p-0 dark:text-blue-400',
    ];

    $sizeClasses = [
        'xs' => 'px-2 py-1 text-xs',
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
        'xl' => 'px-8 py-4 text-lg',
    ];

    $iconSizes = [
        'xs' => 'w-3 h-3',
        'sm' => 'w-4 h-4',
        'md' => 'w-4 h-4',
        'lg' => 'w-5 h-5',
        'xl' => 'w-6 h-6',
    ];

    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg border transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer';

    $currentVariantClass = $variantClasses[$variantKey] ?? $variantClasses['primary'];
    $currentSizeClass = $sizeClasses[$sizeKey] ?? $sizeClasses['md'];
    $currentIconSize = $iconSizes[$sizeKey] ?? $iconSizes['md'];

    $focusRingColor = match ($variantKey) {
        'primary', 'outline-primary' => 'focus:ring-orange-500',
        'secondary', 'outline-secondary' => 'focus:ring-gray-500',
        'success' => 'focus:ring-green-500',
        'warning' => 'focus:ring-yellow-500',
        'danger', 'outline-danger' => 'focus:ring-red-500',
        'ghost' => 'focus:ring-gray-500',
        'link' => '',
        default => 'focus:ring-orange-500',
    };

    $classes = "{$baseClasses} {$currentVariantClass} {$currentSizeClass} {$focusRingColor}";

    if ($fullWidth) {
        $classes .= ' w-full';
    }

    if ($variantKey === 'link') {
        $classes = 'underline text-blue-600 font-medium transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed text-xs';
    }
@endphp

@if ($variantKey === 'link' || $attributes->has('href'))
    <a href="{{ $attributes->get('href', '#') }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} {{ $disabled ? 'disabled' : '' }}>
        @if ($loading)
            <svg class="animate-spin {{ $currentIconSize }} {{ $slot ? 'mr-2' : '' }}" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif

        @if ($icon && $iconPosition === 'left' && ! $loading)
            <svg class="{{ $currentIconSize }} {{ ! $slot ? '' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {{ $icon }}
            </svg>
        @endif

        @if ($slot)
            {{ $slot }}
        @endif

        @if ($icon && $iconPosition === 'right' && ! $loading)
            <svg class="{{ $currentIconSize }} {{ ! $slot ? '' : 'ml-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {{ $icon }}
            </svg>
        @endif
    </button>
@endif
