<x-layout>
    <x-navbar title="Change Password" :includeSidebar="true" :user="Auth::user()">
        <div class="container mx-auto px-4 pb-8 pt-4">
            <div class="max-w-md mx-auto">
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Change Password</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Update your account password</p>
                    </div>

                    <div class="p-6">
                        <livewire:auth.change-password />
                    </div>
                </div>
            </div>
        </div>
    </x-navbar>
</x-layout>
