<x-layout>
    <x-navbar title="Permits" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:admin.permits.dashboard />
        </div>
    </x-navbar>
</x-layout>
