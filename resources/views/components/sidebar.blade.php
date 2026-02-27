@props([
    'user' => null,
    'currentPage' => null
])

@php
    // Define sidebar items based on user type
    $isAdmin = ($user && ((int) $user->user_type) === 1 && (string) session()->get('ui_mode') !== 'user');
    
    if ($isAdmin) {
        // Admin sidebar items
        $sidebarItems = [
            [
                'label' => 'Dashboard',
                'href' => '/admin/home',
                'icon' => 'dashboard',
                'active' => 'admin/home*',
            ],
            [
                'label' => 'Users',
                'href' => '/admin/users',
                'icon' => 'users',
                'active' => 'admin/users*'
            ],
            [
                'label' => 'Locations',
                'href' => '/admin/locations',
                'icon' => 'locations',
                'active' => 'admin/locations*'
            ],
            [
                'label' => 'Permits',
                'href' => '/admin/permits',
                'icon' => 'forms',
                'active' => 'admin/permits*',
            ],
        ];
    } else {
        // Hatchery user sidebar items (limited access)
        $sidebarItems = [
            [
                'label' => 'Permits Today',
                'href' => '/user/home',
                'icon' => 'forms',
                'active' => 'user/home*',
            ],
            [
                'label' => 'Scheduled Permits',
                'href' => '/user/permits/scheduled',
                'icon' => 'scheduled',
                'active' => 'user/permits/scheduled*',
            ],
            [
                'label' => 'My Permits',
                'href' => '/user/permits/my-permits',
                'icon' => 'my_permits',
                'active' => 'user/permits/my-permits*',
            ],
            [
                'label' => 'Cancelled Permits',
                'href' => '/user/permits/cancelled',
                'icon' => 'cancelled',
                'active' => 'user/permits/cancelled*',
            ],
        ];
    }
    
    // Helper function to check if any child is active
    function hasActiveChild($item) {
        if (!isset($item['children'])) return false;
        foreach ($item['children'] as $child) {
            if (request()->is($child['active'] ?? $child['href'])) {
                return true;
            }
        }
        return false;
    }
@endphp

<div x-data="{ 
    isOpen: false,
    isCollapsed: localStorage.getItem('sidebar-collapsed') !== 'false',
    openDropdowns: (() => {
        let stored = JSON.parse(localStorage.getItem('sidebar-dropdowns') || '{}');
        // Auto-open dropdowns with active children
        @foreach($sidebarItems as $item)
            @if(isset($item['dropdown']) && $item['dropdown'] && hasActiveChild($item))
                stored['{{ $item['label'] }}'] = true;
            @endif
        @endforeach
        return stored;
    })(),
    toggleSidebar() {
        this.isCollapsed = !this.isCollapsed;
        localStorage.setItem('sidebar-collapsed', this.isCollapsed);
    },
    toggleMobile() {
        this.isOpen = !this.isOpen;
    },
    closeMobile() {
        this.isOpen = false;
    },
    toggleDropdown(label) {
        this.openDropdowns[label] = !this.openDropdowns[label];
        localStorage.setItem('sidebar-dropdowns', JSON.stringify(this.openDropdowns));
    },
    isDropdownOpen(label) {
        return this.openDropdowns[label] === true;
    }
}" 
@toggle-sidebar.window="toggleSidebar()"
@toggle-mobile.window="toggleMobile()"
class="relative h-screen"
x-cloak>

    <!-- Mobile overlay -->
    <div x-show="isOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600/75 dark:bg-gray-900/75 z-40 lg:hidden"
         @click="closeMobile()">
    </div>

    <!-- Sidebar -->
    <aside :class="[
        'fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-gray-800 shadow-xl transform transition-all duration-300 ease-in-out lg:relative lg:translate-x-0',
        isOpen ? 'translate-x-0' : '-translate-x-full',
        isCollapsed ? 'lg:w-16' : 'lg:w-64',
        'w-64'
    ]" 
    class="h-screen overflow-hidden"
    @click.stop>
        
        <!-- Header -->
        <div class="flex items-center h-16 px-4 border-b border-gray-200 dark:border-gray-700 shrink-0 bg-white dark:bg-gray-800">
            <!-- Logo/Brand - Always visible on mobile, conditional on desktop -->
            <div class="flex items-center flex-1 lg:hidden">
                <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span class="ml-3 text-xl font-bold text-gray-900 dark:text-gray-100">FVPortal</span>
            </div>

            <!-- Desktop logo - shown when not collapsed -->
            <div x-show="!isCollapsed" 
                 x-transition:enter="transition ease-in-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 class="hidden lg:flex items-center flex-1">
                <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span class="ml-3 text-xl font-bold text-gray-900 dark:text-gray-100">FVPortal</span>
            </div>

            <!-- Mobile close button -->
            <button @click="closeMobile()" 
                    class="lg:hidden ml-auto p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <!-- Desktop toggle button - shown when collapsed (centered) -->
            <div x-show="isCollapsed" class="hidden lg:flex items-center justify-center w-full">
                <button @click="toggleSidebar()" 
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

            <!-- Desktop toggle button - shown when not collapsed (right side) -->
            <button x-show="!isCollapsed" @click="toggleSidebar()" 
                    class="hidden lg:block ml-auto p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto bg-white dark:bg-gray-800" x-cloak>
            @foreach($sidebarItems as $item)
                @if(isset($item['dropdown']) && $item['dropdown'])
                    <!-- Dropdown Parent -->
                    <div x-data="{ 
                        showDropdownMenu: false,
                        dropdownPosition: { x: 0, y: 0 },
                        hasActiveChild: {{ hasActiveChild($item) ? 'true' : 'false' }},
                        updateDropdownPosition(buttonEl) {
                            const rect = buttonEl.getBoundingClientRect();
                            this.dropdownPosition = {
                                x: rect.right + 8,
                                y: rect.top + (rect.height / 2)
                            };
                        }
                    }">
                        <button x-ref="dropdownButton"
                               @click="toggleDropdown('{{ $item['label'] }}')"
                               @mouseenter="if(isCollapsed) { showDropdownMenu = true; updateDropdownPosition($refs.dropdownButton); }"
                               @mouseleave="if(isCollapsed) { setTimeout(() => { if(!$refs.dropdownMenu?.matches(':hover')) showDropdownMenu = false; }, 100); }"
                               class="group relative flex items-center w-full px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200"
                               :class="{
                                   'bg-orange-100 text-orange-700 shadow-sm': hasActiveChild && isCollapsed,
                                   'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white': !(hasActiveChild && isCollapsed)
                               }">
                            
                            <!-- Icon -->
                            <div class="shrink-0 w-6 h-6 flex items-center justify-center"
                                 :class="isCollapsed ? 'lg:mx-auto' : ''">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M33.18,26.11,20.35,13.28A9.28,9.28,0,0,0,7.54,2.79l-1.34.59,5.38,5.38L8.76,11.59,3.38,6.21,2.79,7.54A9.27,9.27,0,0,0,13.28,20.35L26.11,33.18a2,2,0,0,0,2.83,0l4.24-4.24A2,2,0,0,0,33.18,26.11Zm-5.66,5.66L13.88,18.12l-.57.16a7.27,7.27,0,0,1-9.31-7,7.2,7.2,0,0,1,.15-1.48l4.61,4.61l5.66-5.66L9.81,4.15a7.27,7.27,0,0,1,8.47,9.16l-.16.57L31.77,27.53Z"></path>
                                    <circle cx="27.13" cy="27.09" r="1.3" transform="translate(-11.21 27.12) rotate(-45)"></circle>
                                </svg>
                            </div>
                            
                            <!-- Text - Always visible on mobile, conditional on desktop -->
                            <span class="ml-3 whitespace-nowrap flex-1 text-left lg:hidden">{{ $item['label'] }}</span>
                            <span x-show="!isCollapsed" 
                                  x-transition:enter="transition ease-in-out duration-200"
                                  x-transition:enter-start="opacity-0 transform scale-95"
                                  x-transition:enter-end="opacity-100 transform scale-100"
                                  class="ml-3 whitespace-nowrap flex-1 text-left hidden lg:block">{{ $item['label'] }}</span>
                            
                            <!-- Chevron - Always visible on mobile, conditional on desktop -->
                            <svg :class="isDropdownOpen('{{ $item['label'] }}') ? 'rotate-180' : ''"
                                 class="w-4 h-4 transition-transform duration-200 lg:hidden"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            <svg x-show="!isCollapsed"
                                 :class="isDropdownOpen('{{ $item['label'] }}') ? 'rotate-180' : ''"
                                 class="w-4 h-4 transition-transform duration-200 hidden lg:block"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu for collapsed desktop state (appears on hover) -->
                        <template x-teleport="body">
                            <div x-ref="dropdownMenu"
                                 x-show="isCollapsed && showDropdownMenu" 
                                 @mouseenter="showDropdownMenu = true"
                                 @mouseleave="showDropdownMenu = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-90"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-90"
                                 :style="`position: fixed; left: ${dropdownPosition.x}px; top: ${dropdownPosition.y}px; transform: translateY(-50%);`"
                                 class="hidden lg:block bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-600 py-2 min-w-50 z-50">
                                <div class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700">
                                    {{ $item['label'] }}
                                </div>
                                @foreach($item['children'] as $child)
                                    <a href="{{ $child['href'] }}" 
                                       class="flex items-center px-3 py-2 text-sm font-medium transition-colors
                                              {{ request()->is($child['active'] ?? $child['href']) 
                                                  ? 'bg-orange-100 text-orange-700' 
                                                  : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                        <span class="whitespace-nowrap">{{ $child['label'] }}</span>
                                    </a>
                                @endforeach
                                <!-- Arrow pointer -->
                                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 rotate-45 w-2 h-2 bg-white dark:bg-gray-800 border-l border-t border-gray-200 dark:border-gray-600"></div>
                            </div>
                        </template>
                        
                        <!-- Dropdown Children (for mobile always, desktop when expanded) -->
                        <div x-show="isDropdownOpen('{{ $item['label'] }}')"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="mt-1 space-y-1 lg:hidden">
                            @foreach($item['children'] as $child)
                                <a href="{{ $child['href'] }}" 
                                   @click="closeMobile()"
                                   class="group relative flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200
                                          {{ request()->is($child['active'] ?? $child['href']) 
                                              ? 'bg-orange-100 text-orange-700 shadow-sm' 
                                              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                                    
                                    <span class="whitespace-nowrap pl-8">{{ $child['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                        
                        <!-- Dropdown Children for desktop expanded -->
                        <div x-show="!isCollapsed && isDropdownOpen('{{ $item['label'] }}')"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="mt-1 space-y-1 hidden lg:block">
                            @foreach($item['children'] as $child)
                                <a href="{{ $child['href'] }}" 
                                   class="group relative flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200
                                          {{ request()->is($child['active'] ?? $child['href']) 
                                              ? 'bg-orange-100 text-orange-700 shadow-sm' 
                                              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
                                    
                                    <span class="whitespace-nowrap pl-8">{{ $child['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <!-- Regular Menu Item -->
                    <a href="{{ $item['href'] }}" 
                       x-data="{ 
                           showTooltip: false,
                           tooltipPosition: { x: 0, y: 0 },
                           updatePosition() {
                               const rect = this.$el.getBoundingClientRect();
                               this.tooltipPosition = {
                                   x: rect.right + 8,
                                   y: rect.top + (rect.height / 2)
                               };
                           }
                       }"
                       @mouseenter="if(isCollapsed) { showTooltip = true; updatePosition(); }"
                       @mouseleave="showTooltip = false"
                       @click="closeMobile()"
                       class="group relative flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200
                              {{ isset($item['customActive']) && $item['customActive']() 
                                  ? 'bg-orange-100 text-orange-700 shadow-sm' 
                                  : (request()->is($item['active'] ?? $item['href']) 
                                      ? 'bg-orange-100 text-orange-700 shadow-sm' 
                                      : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white') }}">
                        
                        <!-- Icon -->
                        <div class="shrink-0 w-6 h-6 flex items-center justify-center"
                             :class="isCollapsed ? 'lg:mx-auto' : ''">
                            @if($item['icon'] === 'dashboard')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                            @elseif($item['icon'] === 'scheduled')
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M12,2 C17.5228475,2 22,6.4771525 22,12 C22,17.5228475 17.5228475,22 12,22 C6.4771525,22 2,17.5228475 2,12 C2,6.4771525 6.4771525,2 12,2 Z M12,4 C7.581722,4 4,7.581722 4,12 C4,16.418278 7.581722,20 12,20 C16.418278,20 20,16.418278 20,12 C20,7.581722 16.418278,4 12,4 Z M12,6 C12.5128358,6 12.9355072,6.38604019 12.9932723,6.88337887 L13,7 L13,11.5857864 L14.7071068,13.2928932 C15.0976311,13.6834175 15.0976311,14.3165825 14.7071068,14.7071068 C14.3466228,15.0675907 13.7793918,15.0953203 13.3871006,14.7902954 L13.2928932,14.7071068 L11.2928932,12.7071068 C11.1366129,12.5508265 11.0374017,12.3481451 11.0086724,12.131444 L11,12 L11,7 C11,6.44771525 11.4477153,6 12,6 Z"/>
                                </svg>
                            @elseif($item['icon'] === 'my_permits')
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M21,20a2,2,0,0,1-2,2H5a2,2,0,0,1-2-2,6,6,0,0,1,6-6h6A6,6,0,0,1,21,20Zm-9-8A5,5,0,1,0,7,7,5,5,0,0,0,12,12Z" />
                                </svg>
                            @elseif($item['icon'] === 'cancelled')
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 455 455" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M227.5,0C101.761,0,0,101.75,0,227.5C0,353.239,101.75,455,227.5,455C353.239,455,455,353.25,455,227.5C455.001,101.761,353.251,0,227.5,0z M310.759,268.333c11.715,11.716,11.715,30.711,0,42.427c-5.858,5.858-13.536,8.787-21.213,8.787s-15.355-2.929-21.213-8.787L227.5,269.927l-40.832,40.832c-5.858,5.858-13.536,8.787-21.213,8.787s-15.355-2.929-21.213-8.787c-11.715-11.716-11.715-30.711,0-42.427l40.832-40.832l-40.832-40.832c-11.715-11.716-11.715-30.711,0-42.427c11.716-11.716,30.711-11.716,42.427,0l40.832,40.832l40.832-40.832c11.716-11.716,30.711-11.716,42.427,0c11.715,11.716,11.715,30.711,0,42.427L269.927,227.5L310.759,268.333z"/>
                                </svg>
                            @elseif($item['icon'] === 'locations')
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2c-4.4 0-8 3.6-8 8 0 5.4 7 11.5 7.3 11.8.2.1.5.2.7.2.2 0 .5-.1.7-.2.3-.3 7.3-6.4 7.3-11.8 0-4.4-3.6-8-8-8zm0 17.7c-2.1-2-6-6.3-6-9.7 0-3.3 2.7-6 6-6s6 2.7 6 6-3.9 7.7-6 9.7zM12 6c-2.2 0-4 1.8-4 4s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4zm0 6c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z" fill="currentColor"/>
                                </svg>
                            @elseif($item['icon'] === 'forms')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            @elseif($item['icon'] === 'users')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            @endif
                        </div>
                        
                        <!-- Text - Always visible on mobile, conditional on desktop -->
                        <span class="ml-3 whitespace-nowrap lg:hidden">{{ $item['label'] }}</span>
                        <span x-show="!isCollapsed" 
                              x-transition:enter="transition ease-in-out duration-200"
                              x-transition:enter-start="opacity-0 transform scale-95"
                              x-transition:enter-end="opacity-100 transform scale-100"
                              class="ml-3 whitespace-nowrap hidden lg:block">{{ $item['label'] }}</span>
                        
                        <!-- Tooltip for collapsed desktop state - teleported to body -->
                        <template x-teleport="body">
                            <div x-show="isCollapsed && showTooltip" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-90"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-90"
                                 :style="`position: fixed; left: ${tooltipPosition.x}px; top: ${tooltipPosition.y}px; transform: translateY(-50%);`"
                                 class="hidden lg:block px-2 py-1 bg-gray-900 text-white text-sm rounded-md whitespace-nowrap pointer-events-none z-50">
                                {{ $item['label'] }}
                                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1 rotate-45 w-2 h-2 bg-gray-900"></div>
                            </div>
                        </template>
                    </a>
                @endif
            @endforeach
        </nav>
    </aside>

    <!-- Mobile menu button -->
    <button @click="toggleMobile()" 
            x-show="!isOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="lg:hidden fixed bottom-4 right-4 z-50 p-3 bg-orange-500 hover:bg-orange-600 text-white rounded-full shadow-lg transition-colors cursor-pointer">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <style>
        /* Prevent flash of unstyled content */
        [x-cloak] {
            display: none !important;
        }
        
        /* Custom scrollbar styles */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }
        
        .scrollbar-track-transparent::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .scrollbar-thumb-gray-300::-webkit-scrollbar-thumb {
            background-color: #d1d5db;
            border-radius: 3px;
        }
        
        .scrollbar-thumb-gray-300::-webkit-scrollbar-thumb:hover {
            background-color: #9ca3af;
        }

        /* Prevent body scroll when mobile menu is open */
        body:has([x-data] [x-show="isOpen"]) {
            overflow: hidden;
        }
    </style>
</div>
