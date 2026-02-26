@props([
    'label' => '',
    'name' => '',
    'errorKey' => null,
    'value' => '',
    'placeholder' => 'Enter text here',
    'required' => false,
    'type' => 'text',
    'min' => null,
    'max' => null,
    'class' => '',
    'icon' => '',
    'wireModel' => null,
    'subtext' => ''
])

@php
    $errorKey = $errorKey ?: $name;
@endphp

<div class="mb-6 {{ $class }}">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
        @if($subtext)
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{!! $subtext !!}</p>
        @endif
    @endif

    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($icon === 'user')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    @elseif($icon === 'lock')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    @endif
                </svg>
            </div>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            @if($wireModel)
                wire:model="{{ $wireModel }}"
            @else
                value="{{ old($name, $value) }}"
            @endif
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($min !== null) min="{{ $min }}" @endif
            @if($max !== null) max="{{ $max }}" @endif
            class="mt-1 block w-full rounded-lg border shadow-sm {{ $icon ? 'pl-10' : 'px-4' }} py-2
            {{ $errors->has($errorKey)
                ? 'border-red-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-red-500 focus:ring-red-200'
                : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50' }}
            autofill:bg-white autofill:text-gray-900 dark:autofill:bg-gray-800 dark:autofill:text-white"
        >

        @if($type === 'password')
            <button type="button" onclick="togglePassword('{{ $name }}')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg id="{{ $name }}-eye" class="h-5 w-5 text-gray-400 dark:text-gray-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg id="{{ $name }}-eye-off" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
            </button>
        @endif
    </div>

    @error($errorKey)
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

@if($type === 'password')
<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const eyeIcon = document.getElementById(inputId + '-eye');
    const eyeOffIcon = document.getElementById(inputId + '-eye-off');

    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.classList.remove('hidden');
        eyeOffIcon.classList.add('hidden');
    } else {
        input.type = 'password';
        eyeIcon.classList.add('hidden');
        eyeOffIcon.classList.remove('hidden');
    }
}
</script>
@endif
