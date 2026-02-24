<div x-data="{ 
    isDark: false,
    init() {
        // Check for saved preference or system preference
        const saved = localStorage.getItem('dark_mode');
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        this.isDark = saved === 'true' || (!saved && systemDark);
        
        // Apply initial theme using data attribute
        document.documentElement.setAttribute('data-theme', this.isDark ? 'dark' : 'light');
        
        // Watch for system theme changes
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (!localStorage.getItem('dark_mode')) {
                    this.isDark = e.matches;
                    document.documentElement.setAttribute('data-theme', this.isDark ? 'dark' : 'light');
                }
            });
        }
        
        // Listen for custom theme change events
        window.addEventListener('themeChanged', () => {
            this.isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        });
    },
    toggle() {
        this.isDark = !this.isDark;
        const newTheme = this.isDark ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('dark_mode', this.isDark ? 'true' : 'false');
        // Also set cookie for server-side detection
        document.cookie = `dark_mode=${this.isDark ? 'true' : 'false'}; path=/; max-age=31536000`; // 1 year
        
        // Dispatch event to notify other components
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: newTheme } }));
    }
}" class="relative">
    <!-- Dark Mode Toggle Button -->
    <button 
        @click="toggle()"
        class="p-2 cursor-pointer text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg transition-colors duration-200"
        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
    >
        <!-- Sun Icon (visible in dark mode) -->
        <svg x-show="isDark" x-cloak 
            class="w-5 h-5" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
            </path>
        </svg>
        
        <!-- Moon Icon (visible in light mode) -->
        <svg x-show="!isDark" x-cloak 
            class="w-5 h-5" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z">
            </path>
        </svg>
    </button>
</div>
