@props(['hideDate' => false, 'includeSidebar' => false, 'user' => null, 'title' => null])

@php
    $isAdmin = ($user && ((int) $user->user_type) === 1);
    $displayName = $user
        ? trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''))
        : '';
    $displayName = $displayName !== '' ? $displayName : (string) ($user->username ?? '');
    $displayUsername = (string) ($user->username ?? '');
@endphp

@auth
    @if($includeSidebar && $user)
        <div class="flex h-screen bg-gray-100 dark:bg-gray-900">
            <!-- Sidebar -->
            <x-sidebar :user="$user" />
            
            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Navbar -->
                <nav class="shadow-lg border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 sticky top-0 z-30">
                    <div class="mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center h-16">
                            <!-- Left side - Breadcrumbs -->
                            <div class="flex items-center space-x-3 flex-1">
                                <!-- Breadcrumb Navigation -->
                                <nav class="flex items-center space-x-1 text-sm" aria-label="Breadcrumb">
                                    <ol class="flex items-center space-x-1">
                                        <li>
                                            <a href="{{ $isAdmin ? route('admin.home') : route('user.home') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                                                @if($isAdmin)
                                                    <!-- Admin: Dashboard icon -->
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                                    </svg>
                                                @else
                                                    <!-- Regular User: Forms icon -->
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                @endif
                                            </a>
                                        </li>
                                        
                                        <!-- Admin Dashboard breadcrumb for admin dashboard pages (except when already on admin dashboard) -->
                                        @if($isAdmin && request()->is('admin/*dashboard*') && !request()->is('admin/dashboard'))
                                            <li class="flex items-center">
                                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                <a href="{{ route('admin.home') }}" class="ml-1 text-gray-500 hover:text-gray-700 transition-colors">Admin Dashboard</a>
                                            </li>
                                        @endif
                                        
                                        @if($title)
                                            <li class="flex items-center">
                                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="ml-1 text-gray-900 dark:text-gray-100 font-medium">{{ $title }}</span>
                                            </li>
                                        @endif
                                    </ol>
                                </nav>
                                
                                <!-- Navbar Actions (only show with sidebar) -->
                                @if(isset($navbarActions))
                                    <div class="ml-4">
                                        {{ $navbarActions }}
                                    </div>
                                @endif
                            </div>

                            <!-- Right side - User Profile Dropdown -->
                            <div class="flex items-center space-x-3">
                                <!-- Dark Mode Toggle -->
                                <x-dark-mode-toggle />
                                
                                <!-- User Profile Dropdown -->
                                <div class="relative" x-data="{ open: false, showLogoutConfirmation: false }" x-init="$watch('open', value => value ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'))">
                                <button 
                                    @click="open = !open"
                                    class="flex items-center space-x-3 cursor-pointer text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none rounded-xl p-2 pr-3 transition-all duration-200"
                                    aria-expanded="false"
                                    aria-haspopup="true"
                                >
                                    <div class="relative">
                                        <div class="w-10 h-10 bg-orange-500 dark:bg-orange-600 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-md">
                                            {{ strtoupper(substr($user->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}
                                        </div>
                                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                                    </div>
                                    
                                    <div class="text-left">
                                        <div class="font-semibold text-gray-900 dark:text-gray-100 text-sm hidden sm:block">
                                            {{ $displayName }}
                                        </div>
                                        @if($displayUsername !== '')
                                            <div class="text-xs text-gray-500 dark:text-gray-400 hidden sm:block">
                                                {{ $displayUsername }}
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <svg 
                                        class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform duration-200 hidden sm:block"
                                        :class="{ 'rotate-180': open }"
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div 
                                    x-show="open" 
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="transform opacity-0 scale-95 translate-y-1"
                                    x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                                    x-transition:leave-end="transform opacity-0 scale-95 translate-y-1"
                                    @click.away="open = false"
                                    class="absolute right-0 mt-3 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50"
                                    x-cloak
                                >
                                    <div class="px-4 py-3 bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-12 h-12 bg-orange-500 dark:bg-orange-600 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-md">
                                                {{ strtoupper(substr($user->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                                    {{ $displayName }}
                                                </p>
                                                @if($displayUsername !== '')
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                        {{ $displayUsername }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-t border-gray-200">
                                        <a href="{{ $isAdmin ? route('admin.change-password') : route('user.change-password') }}" class="flex items-center w-full px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100 transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                            </svg>
                                            Change Password
                                        </a>
                                        <button
                                            type="button"
                                            @click="open = false; showLogoutConfirmation = true"
                                            class="flex items-center w-full px-4 py-3 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900 hover:text-red-700 dark:hover:text-red-300 transition-colors duration-200 cursor-pointer"
                                        >
                                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Logout
                                        </button>
                                    </div>
                                </div>

                                <!-- Logout Confirmation Modal -->
                                <div x-show="showLogoutConfirmation"
                                    x-cloak
                                    class="fixed inset-0 z-50 overflow-y-auto"
                                    style="display: none;">
                                    <div class="fixed inset-0 bg-black/50" @click="showLogoutConfirmation = false"></div>
                                    <div class="relative min-h-screen flex items-center justify-center p-4">
                                        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                                            <div class="text-center">
                                                <div class="mx-auto mb-4 text-yellow-500 w-16 h-16">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                    </svg>
                                                </div>
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Log Out?</h3>
                                                <p class="text-gray-700 dark:text-gray-300 mb-4">Are you sure you want to log out of your account?</p>
                                                <div class="flex gap-3 justify-center">
                                                    <button @click="showLogoutConfirmation = false" type="button"
                                                            class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                        Stay Logged In
                                                    </button>
                                                    <form method="POST" action="{{ route('logout') }}">
                                                        @csrf
                                                        <button type="submit"
                                                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                                            Yes, Log Out
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </nav>
                
                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900">
                    {{ $slot }}
                </main>
            </div>
        </div>
    @else
        <!-- Navbar without Sidebar -->
        <nav class="shadow-lg border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 sticky top-0 z-30">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Left side - Breadcrumbs -->
                    <div class="flex items-center space-x-3">
                        <!-- Breadcrumb Navigation -->
                        <nav class="flex items-center space-x-1 text-sm" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-1">
                                <li>
                                    <a href="{{ $isAdmin ? route('admin.home') : route('user.home') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                                        @if($isAdmin)
                                            <!-- Admin: Dashboard icon -->
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                            </svg>
                                        @else
                                            <!-- Regular User: Forms icon -->
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        @endif
                                    </a>
                                </li>
                                
                                <!-- Admin Dashboard breadcrumb for admin dashboard pages (except when already on admin dashboard) -->
                                @if($isAdmin && request()->is('admin/*dashboard*') && !request()->is('admin/dashboard'))
                                    <li class="flex items-center">
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <a href="{{ route('admin.home') }}" class="ml-1 text-gray-500 hover:text-gray-700 transition-colors">Admin Dashboard</a>
                                    </li>
                                @endif
                                
                                @if($title)
                                    <li class="flex items-center">
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="ml-1 text-gray-900 font-medium">{{ $title }}</span>
                                    </li>
                                @endif
                            </ol>
                        </nav>
                    </div>

                    <!-- Right side - User Profile Dropdown -->
                    <div class="flex items-center space-x-3">
                        <!-- Dark Mode Toggle -->
                        <x-dark-mode-toggle />
                        
                        <!-- User Profile Dropdown -->
                        <div class="relative" x-data="{ open: false, showLogoutConfirmation: false }" x-init="$watch('open', value => value ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'))">
                        <button 
                            @click="open = !open"
                            class="flex items-center space-x-3 cursor-pointer text-gray-700 hover:bg-gray-100 focus:outline-none rounded-xl p-2 pr-3 transition-all duration-200"
                            aria-expanded="false"
                            aria-haspopup="true"
                        >
                            <div class="relative">
                                <img src="{{ auth()->user()->profile_photo ? asset('storage/' . auth()->user()->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->first_name) . '+' . urlencode(auth()->user()->last_name) . '&background=' . urlencode(auth()->user()->profile_background ?? 'FFA07A') }}" 
                                class="w-10 h-10 rounded-full object-cover" alt="{{ auth()->user()->full_name }}">
                            </img>
                            
                            <div class="hidden sm:block text-left">
                                <div class="font-semibold text-gray-900 text-sm">
                                    {{ auth()->user()->full_name }}
                                </div>
                            </div>
                            
                            <svg 
                                class="w-4 h-4 text-gray-500 transition-transform duration-200"
                                :class="{ 'rotate-180': open }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div 
                            x-show="open" 
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="transform opacity-0 scale-95 translate-y-1"
                            x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="transform opacity-0 scale-95 translate-y-1"
                            @click.away="open = false"
                            class="absolute right-0 mt-3 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50"
                            x-cloak
                        >
                            <div class="px-4 py-3 bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center space-x-3">
                                    <img src="{{ auth()->user()->profile_photo ? asset('storage/' . auth()->user()->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->first_name) . '&' . urlencode(auth()->user()->last_name) . '&background=' . urlencode(auth()->user()->profile_background ?? 'FFA07A') }}" 
                                    class="w-12 h-12 rounded-full object-cover" alt="{{ auth()->user()->full_name }}">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                        {{ auth()->user()->full_name }}
                                    </p>
                                </div>
                            </div>

                            <div class="border-t border-gray-200">
                                <a href="{{ $isAdmin ? route('admin.change-password') : route('user.change-password') }}" class="flex items-center w-full px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100 transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                    Change Password
                                </a>
                                <button
                                    type="button"
                                    @click="open = false; showLogoutConfirmation = true"
                                    class="flex items-center w-full px-4 py-3 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900 hover:text-red-700 dark:hover:text-red-300 transition-colors duration-200 cursor-pointer"
                                >
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Logout
                                </button>
                            </div>
                        </div>

                        <!-- Logout Confirmation Modal -->
                        <div x-show="showLogoutConfirmation"
                            x-cloak
                            class="fixed inset-0 z-50 overflow-y-auto"
                            style="display: none;">
                            <div class="fixed inset-0 bg-black/50" @click="showLogoutConfirmation = false"></div>
                            <div class="relative min-h-screen flex items-center justify-center p-4">
                                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                                    <div class="text-center">
                                        <div class="mx-auto mb-4 text-yellow-500 w-16 h-16">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Log Out?</h3>
                                        <p class="text-gray-700 dark:text-gray-300 mb-4">Are you sure you want to log out of your account?</p>
                                        <div class="flex gap-3 justify-center">
                                            <button @click="showLogoutConfirmation = false" type="button"
                                                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                Stay Logged In
                                            </button>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit"
                                                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                                    Yes, Log Out
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Page Content for non-sidebar layout -->
        <main class="bg-gray-50 dark:bg-gray-900">
            {{ $slot }}
        </main>
    @endif
@endauth

<style>
    [x-cloak] {
        display: none !important;
    }
    
    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
