<x-layout>
    <x-navbar title="Permits Today" :includeSidebar="true" :user="Auth::user()">
        <livewire:permit-dashboard />
    </x-navbar>
</x-layout>