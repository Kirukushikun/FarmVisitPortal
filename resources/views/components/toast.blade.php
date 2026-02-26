@props(['messages' => []])

@php
    $messages = $messages ?: [
        'error' => session('error'),
        'success' => session('success'),
        'warning' => session('warning'),
        'info' => session('info'),
    ];
    $messages = array_filter($messages);
@endphp

<div wire:ignore 
     x-data="{ 
         toasts: [],
         init() {
             @foreach($messages as $type => $message)
                this.showToast('{{ $message }}', '{{ $type }}');
             @endforeach
             
             window.addEventListener('showToast', (event) => {
                 this.showToast(event.detail.message, event.detail.type);
             });
         },
         showToast(message, type = 'info') {
             const id = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
             this.toasts.push({ message, type, id });
             setTimeout(() => {
                 this.toasts = this.toasts.filter(t => t.id !== id);
             }, 5000);
         }
     }" 
     class="fixed top-4 right-4 z-50 space-y-2">
    
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transform ease-in duration-200 transition"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             :class="{
                 'bg-green-500 text-white': toast.type === 'success',
                 'bg-red-500 text-white': toast.type === 'error',
                 'bg-yellow-500 text-white': toast.type === 'warning',
                 'bg-blue-500 text-white': toast.type === 'info'
             }"
             class="px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 min-w-80 max-w-md">
           
            <div x-show="toast.type === 'success'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <div x-show="toast.type === 'error'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <div x-show="toast.type === 'warning'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <div x-show="toast.type === 'info'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            
            <span class="flex-1" x-text="toast.message"></span>
            
            <button @click="toasts = toasts.filter(t => t.id !== toast.id)" 
                    class="shrink-0 hover:opacity-75 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </template>
</div>
