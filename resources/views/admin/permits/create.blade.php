<x-layout>
    <x-navbar :breadcrumbs="[
        ['label' => 'Permits', 'href' => route('admin.permits.index')],
        ['label' => 'Create Permit'],
    ]" :includeSidebar="true" :user="Auth::user()">
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <div class="p-4">
                <div class="w-full max-w-lg bg-white dark:bg-gray-800 rounded-xl shadow-lg px-8 pt-6 pb-2 mx-auto">
                    <livewire:admin.permits.create />
                </div>
            </div>
        </div>
    </x-navbar>
</x-layout>
