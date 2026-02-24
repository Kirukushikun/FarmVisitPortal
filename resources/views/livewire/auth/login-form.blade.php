<div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-sm">
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 p-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Farm Visit Portal</h1>
            </div>

            <form wire:submit.prevent="submit" class="space-y-4">
                <div>
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-medium text-gray-600">Sign in as</label>
                        <div class="flex items-center rounded-full bg-gray-100 p-0.5 ring-1 ring-gray-200">
                            <button
                                type="button"
                                wire:click="$set('role','user')"
                                class="rounded-full px-2.5 py-1 text-xs font-medium transition {{ $role === 'user' ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200' : 'text-gray-600 hover:text-gray-900' }}"
                                aria-pressed="{{ $role === 'user' ? 'true' : 'false' }}"
                            >
                                User
                            </button>
                            <button
                                type="button"
                                wire:click="$set('role','admin')"
                                class="rounded-full px-2.5 py-1 text-xs font-medium transition {{ $role === 'admin' ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200' : 'text-gray-600 hover:text-gray-900' }}"
                                aria-pressed="{{ $role === 'admin' ? 'true' : 'false' }}"
                            >
                                Admin
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="username" class="block text-xs font-medium text-gray-600">Username</label>
                    <input
                        id="username"
                        type="text"
                        wire:model.defer="username"
                        autocomplete="username"
                        required
                        class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @php($loginError = $errors->first('username') ?: $errors->first('role'))
                    @if ($loginError)
                        <p class="mt-2 text-xs text-red-600">{{ $loginError }}</p>
                    @endif
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-gray-600">Password</label>
                    <div class="mt-2 relative">
                        <input
                            id="password"
                            type="{{ $showPassword ? 'text' : 'password' }}"
                            wire:model.defer="password"
                            autocomplete="current-password"
                            required
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 pr-10 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >

                        <button
                            type="button"
                            wire:click="$toggle('showPassword')"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                            aria-label="Toggle password visibility"
                        >
                            @if ($showPassword)
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                    <path d="M10.733 5.08A10.784 10.784 0 0 1 12 5c6.5 0 10 7 10 7a19.5 19.5 0 0 1-3.2 4.3"/>
                                    <path d="M6.61 6.61A19.5 19.5 0 0 0 2 12s3.5 7 10 7a10.8 10.8 0 0 0 5.39-1.61"/>
                                    <path d="M14.12 14.12A3 3 0 0 1 9.88 9.88"/>
                                    <path d="M1 1l22 22"/>
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            @endif
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Login
                </button>
            </form>
        </div>
    </div>
</div>
