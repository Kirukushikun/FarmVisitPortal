<x-layout>
    <x-navbar title="Locations" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <div>
                <livewire:admin.location-management.display />
            </div>
        </div>
    </x-navbar>
</x-layout>
