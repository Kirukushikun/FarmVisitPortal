@props([
    'label' => '',
    'name' => '',
    'errorKey' => null,
    'required' => false,
    'placeholder' => 'Select an option',
    'options' => []
])

@php
    $errorKey = $errorKey ?: $name;
    $options = $options ?? [];
    $displayValues = [];

    if (is_array($options)) {
        foreach ($options as $value => $display) {
            $displayValues[$value] = $display;
        }
    }
    elseif (is_array($options) && !isset($options[0])) {
        $displayValues = $options;
    }
@endphp

<style>
    select:invalid { color: #9ca3af; }
    select option { color: black; }
    [data-theme=dark] select:invalid { color: #6b7280; }
    [data-theme=dark] select option { color: white !important; }
</style>

<div class="mb-6">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <select 
        id="{{ $name }}"
        name="{{ $name }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => "mt-1 block w-full rounded-lg border px-4 py-2 shadow-sm cursor-pointer " . (
            $errors->has($errorKey)
                ? 'border-red-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-red-500 focus:ring-red-200'
                : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50'
        )]) }}
    >
        <option value="" disabled selected hidden class="text-gray-900 dark:text-gray-100">{{ $placeholder }}</option>
        @foreach($displayValues as $value => $display)
            <option value="{{ $value }}" class="text-black dark:text-white {{ $attributes->get('class') }}">
                {{ $display }}
            </option>
        @endforeach
        {{ $slot }}
    </select>

    @error($errorKey)
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
