<x-layout>
    <x-navbar title="Dashboard" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <livewire:admin.home-dashboard />
        </div>
    </x-navbar>
</x-layout>