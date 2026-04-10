<div>
    @if ($showModal)
        <div class="fixed inset-0 z-9999 flex items-center justify-center p-4" wire:ignore.self>
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>

            <div class="relative w-full max-w-md p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add New Admin</h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="createUser">
                    <div class="mb-4">
                        <x-text-input
                            label="First Name"
                            name="firstName"
                            type="text"
                            :wireModel="'firstName'"
                            placeholder="Enter first name"
                        />
                    </div>

                    <div class="mb-4">
                        <x-text-input
                            label="Last Name"
                            name="lastName"
                            type="text"
                            :wireModel="'lastName'"
                            placeholder="Enter last name"
                        />
                    </div>

                    <div class="mb-6">
                        <x-dropdown
                            label="Department"
                            name="department"
                            error-key="department"
                            placeholder="Select department"
                            wire:model.live="department"
                            required
                        >
                            <option value="">Select Department</option>
                            <option value="FOC">FOC</option>
                            <option value="FEEDMILL">FEEDMILL</option>
                            <option value="GENERAL SERVICES">GENERAL SERVICES</option>
                            <option value="HUMAN RESOURCES">HUMAN RESOURCES</option>
                            <option value="IT & SECURITY">IT & SECURITY</option>
                            <option value="POULTRY">POULTRY</option>
                            <option value="PURCHASING">PURCHASING</option>
                            <option value="SALES & ANALYTICS">SALES & ANALYTICS</option>
                            <option value="SWINE">SWINE</option>
                        </x-dropdown>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <x-button
                            variant="outline-secondary"
                            size="sm"
                            wire:click="closeModal"
                            wire:loading.attr="disabled"
                            wire:target="closeModal"
                        >
                            <span wire:target="closeModal">Cancel</span>
                        </x-button>
                        <x-button
                            variant="primary"
                            size="sm"
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="createUser"
                        >
                            <span wire:loading.remove wire:target="createUser">Create Admin</span>
                            <span wire:loading.inline-flex wire:target="createUser" class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Creating...
                            </span>
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
