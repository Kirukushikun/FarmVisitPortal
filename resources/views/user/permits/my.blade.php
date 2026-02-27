<x-layout>
    <x-navbar title="My Permits" :includeSidebar="true" :user="Auth::user()">
        <livewire:my-permits />
    </x-navbar>
</x-layout>
