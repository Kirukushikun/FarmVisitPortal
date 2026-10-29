@php
    $isAdmin = in_array((int) (Auth::user()->user_type ?? 0), [1, 2], true);
    // Guards: anywhere after login. Admins/creators: only inside the permit module.
    $showReminder = Auth::check() && (! $isAdmin || request()->routeIs('admin.permits.*', 'user.permits.*'));
@endphp
@if ($showReminder)
    <div
        x-data="{
            open: false,
            skipToday: false,
            key: 'photo_policy_skip_{{ Auth::id() }}',
            sessionKey: 'photo_policy_seen_{{ Auth::id() }}',
            today() {
                const d = new Date();
                return d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();
            },
            init() {
                try {
                    if (localStorage.getItem(this.key) === this.today()) return;
                    if (sessionStorage.getItem(this.sessionKey)) return;
                } catch (e) {}
                this.open = true;
            },
            dismiss() {
                try {
                    sessionStorage.setItem(this.sessionKey, '1');
                    if (this.skipToday) {
                        localStorage.setItem(this.key, this.today());
                    }
                } catch (e) {}
                this.open = false;
            }
        }"
        x-show="open"
        style="display: none;"
        x-on:keydown.escape.window="dismiss()"
        class="fixed inset-0 z-9999 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="photo-policy-title"
    >
        <div class="fixed inset-0 bg-black/60"></div>

        <div class="relative w-full max-w-md p-6 bg-white dark:bg-gray-800 shadow-xl dark:shadow-2xl rounded-lg">
            <div class="flex items-center mb-4">
                <div class="shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M9.88 5.12A2 2 0 0111.5 4h1a2 2 0 011.6.8L15 6h2a2 2 0 012 2v7m-2.5 2.5A2 2 0 0115 18H7a2 2 0 01-2-2V8a2 2 0 012-2h.5M10.5 10.5a3 3 0 004 4" />
                    </svg>
                </div>
                <h3 id="photo-policy-title" class="ml-4 text-lg font-semibold text-gray-900 dark:text-white">No Photography Allowed</h3>
            </div>

            <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                Taking pictures or videos is <strong>strictly prohibited</strong> at all farm locations in this portal, without exception.
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                This applies to everyone: guards, permit creators, and visitors.
            </p>

            <label class="flex items-center mb-5 text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                <input type="checkbox" x-model="skipToday" class="mr-2 h-4 w-4 rounded border-gray-300 dark:border-gray-600">
                Don't show again today
            </label>

            <div class="flex justify-end">
                <button type="button" x-on:click="dismiss()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md cursor-pointer">
                    I understand
                </button>
            </div>
        </div>
    </div>
@endif
