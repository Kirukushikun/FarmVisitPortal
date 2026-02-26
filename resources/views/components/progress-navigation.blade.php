@props([
    'currentStep' => 1,
    'visibleStepIds' => [1],
    'canProceed' => true,
    'isLastVisibleStep' => false,
    'showProgress' => false,
])

@php
    $visibleStepIds = array_values(array_unique(array_map('intval', $visibleStepIds ?? [1])));
    sort($visibleStepIds);

    $currentIndex = array_search((int) $currentStep, $visibleStepIds, true);
    $currentIndex = $currentIndex === false ? 0 : $currentIndex;
@endphp

<div>
    <div class="space-y-4">
        {{ $slot }}
    </div>

    <div class="flex justify-between items-center my-6 w-full max-w-lg">

        <div class="flex items-center">
            @if(((int) $currentStep) > 1)
                <x-button
                    wire:click="previousStep"
                    wire:loading.attr="disabled"
                    wire:target="previousStep,nextStep,submitForm"
                    variant="outline-secondary"
                    type="button"
                >
                    <span wire:loading.remove wire:target="previousStep">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </span>
                    <span wire:loading wire:target="previousStep" class="inline-flex items-center">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </x-button>
            @else
                <div class="w-8"></div>
            @endif
        </div>

        @if($showProgress)
            <div class="flex items-center">
                @foreach($visibleStepIds as $index => $stepId)
                    @php
                        $stepId = (int) $stepId;
                        $isActiveOrComplete = $currentIndex >= $index;
                        $isNext = ($currentIndex + 1) === $index;

                        $dotClass = $isActiveOrComplete
                            ? 'bg-orange-500'
                            : ($isNext ? 'bg-gray-300' : 'bg-white border-2 border-gray-300');

                        $lineClass = ($currentIndex > $index) ? 'bg-orange-500' : 'bg-gray-300';
                    @endphp
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full {{ $dotClass }}"></div>
                        @if($index < (count($visibleStepIds) - 1))
                            <div class="w-4 h-0.5 {{ $lineClass }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex items-center">
            @if(!$isLastVisibleStep)
                <x-button
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    wire:target="previousStep,nextStep,submitForm"
                    variant="primary"
                    type="button"
                    :disabled="!$canProceed"
                >
                    <span wire:loading.remove wire:target="nextStep" class="inline-flex items-center">
                        Next
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                    <span wire:loading wire:target="nextStep" class="inline-flex items-center">
                        <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Next...
                    </span>
                </x-button>
            @else
                <x-button
                    wire:click="submitForm"
                    wire:loading.attr="disabled"
                    wire:target="previousStep,nextStep,submitForm"
                    variant="success"
                    type="button"
                >
                    <span wire:loading.remove wire:target="submitForm" class="inline-flex items-center">Submit</span>
                    <span wire:loading wire:target="submitForm" class="inline-flex items-center">
                        <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Submitting...
                    </span>
                </x-button>
            @endif
        </div>

    </div>
</div>
