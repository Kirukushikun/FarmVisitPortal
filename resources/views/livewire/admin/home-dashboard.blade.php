<div x-data="{ range: @entangle('range') }" wire:poll.30s="refreshDashboard" class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Admin Dashboard</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Permit statistics</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
        <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">New Permits / Users / Locations ({{ ucfirst($range) }})</h3>

                <div class="flex w-full sm:w-auto rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                    <button type="button" wire:click="setRange('week')" :class="range === 'week' ? 'bg-orange-600 text-white' : 'bg-transparent text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'" class="flex-1 sm:flex-none px-4 py-2.5 text-xs font-medium transition-colors cursor-pointer text-center whitespace-nowrap">
                        Week
                    </button>
                    <button type="button" wire:click="setRange('month')" :class="range === 'month' ? 'bg-orange-600 text-white' : 'bg-transparent text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'" class="flex-1 sm:flex-none px-4 py-2.5 text-xs font-medium transition-colors cursor-pointer text-center whitespace-nowrap">
                        Month
                    </button>
                    <button type="button" wire:click="setRange('year')" :class="range === 'year' ? 'bg-orange-600 text-white' : 'bg-transparent text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'" class="flex-1 sm:flex-none px-4 py-2.5 text-xs font-medium transition-colors cursor-pointer text-center whitespace-nowrap">
                        Year
                    </button>
                </div>
            </div>
            <div class="relative h-80" wire:ignore>
                <canvas id="fvAdminHomeTotalPermitsChart"></canvas>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">New Permits / Users / Locations</h3>

                <div class="flex rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                    <button type="button" wire:click="setPieRange('year')" class="flex-1 px-3 py-2 text-xs font-medium transition-colors cursor-pointer text-center whitespace-nowrap {{ ($pieRange ?? 'year') === 'year' ? 'bg-orange-600 text-white' : 'bg-transparent text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        Year
                    </button>
                    <button type="button" wire:click="setPieRange('overall')" class="flex-1 px-3 py-2 text-xs font-medium transition-colors cursor-pointer text-center whitespace-nowrap {{ ($pieRange ?? 'year') === 'overall' ? 'bg-orange-600 text-white' : 'bg-transparent text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        Overall
                    </button>
                </div>
            </div>

            <div class="relative h-80" wire:ignore>
                <canvas id="fvAdminHomePermitsPieChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between mb-4">
                <div class="text-sm font-semibold text-gray-900 dark:text-white">Scheduled Calendar</div>
                @php
                    $month = (string) ($calendar['month'] ?? now()->format('Y-m'));
                    $monthLabel = (string) ($calendar['month_label'] ?? '');
                    $prevMonth = \Carbon\Carbon::createFromFormat('Y-m', $month)->subMonth()->format('Y-m');
                    $nextMonth = \Carbon\Carbon::createFromFormat('Y-m', $month)->addMonth()->format('Y-m');
                @endphp
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="setCalendarMonth('{{ $prevMonth }}')" class="px-3 py-1.5 text-xs rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Prev
                    </button>
                    <div class="text-xs font-medium text-gray-700 dark:text-gray-200">{{ $monthLabel }}</div>
                    <button type="button" wire:click="setCalendarMonth('{{ $nextMonth }}')" class="px-3 py-1.5 text-xs rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Next
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                <div class="text-center">Sun</div>
                <div class="text-center">Mon</div>
                <div class="text-center">Tue</div>
                <div class="text-center">Wed</div>
                <div class="text-center">Thu</div>
                <div class="text-center">Fri</div>
                <div class="text-center">Sat</div>
            </div>

            <div class="grid grid-cols-7 gap-2">
                @foreach(($calendar['grid'] ?? []) as $day)
                    @php
                        $counts = $day['counts'] ?? [];
                        $total = (int) ($counts['total'] ?? 0);
                        $muted = !($day['in_month'] ?? false);
                        $selected = (bool) ($day['is_selected'] ?? false);
                    @endphp
                    <button
                        type="button"
                        wire:click="selectDay('{{ $day['date'] }}')"
                        class="rounded-lg border p-2 text-left transition-colors
                            {{ $muted ? 'border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-400' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700' }}
                            {{ $selected ? 'ring-2 ring-orange-500' : '' }}"
                    >
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-semibold {{ ($day['is_today'] ?? false) ? 'text-orange-600 dark:text-orange-400' : '' }}">{{ (int) ($day['day'] ?? 0) }}</div>
                            <div class="text-[10px] px-1.5 py-0.5 rounded bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300">
                                {{ $total }}
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Day Details</div>

            @php
                $selected = $calendar['selected'] ?? null;
                $selectedCounts = is_array($selected) ? ($selected['counts'] ?? []) : [];
            @endphp

            @if($selected)
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $selected['date'] ?? '' }}</div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <div class="text-gray-700 dark:text-gray-200">Total</div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ (int) ($selectedCounts['total'] ?? 0) }}</div>
                    </div>

                    @if(($selected['date'] ?? '') > now()->toDateString())
                        <div class="flex items-center justify-between text-sm">
                            <div class="text-gray-700 dark:text-gray-200">Scheduled</div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ (int) ($selectedCounts['scheduled'] ?? 0) }}</div>
                        </div>
                    @elseif(($selected['date'] ?? '') === now()->toDateString())
                        <div class="flex items-center justify-between text-sm">
                            <div class="text-gray-700 dark:text-gray-200">In Progress</div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ (int) ($selectedCounts['in_progress'] ?? 0) }}</div>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <div class="text-gray-700 dark:text-gray-200">Received</div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ (int) ($selectedCounts['received'] ?? 0) }}</div>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <div class="text-gray-700 dark:text-gray-200">Cancelled</div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ (int) ($selectedCounts['cancelled'] ?? 0) }}</div>
                        </div>
                    @else
                        <div class="flex items-center justify-between text-sm">
                            <div class="text-gray-700 dark:text-gray-200">Received</div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ (int) ($selectedCounts['received'] ?? 0) }}</div>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <div class="text-gray-700 dark:text-gray-200">Cancelled</div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ (int) ($selectedCounts['cancelled'] ?? 0) }}</div>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-sm text-gray-600 dark:text-gray-400">Select a day on the calendar to see counts.</div>
            @endif
        </div>
    </div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @endonce

    <script>
        (function () {
            const chartsData = @json($charts);
            const initialRange = @json($range);
            const pieData = @json($pie ?? []);
            const initialPieRange = @json($pieRange ?? 'year');

            function buildLine(ctx, data) {
                return new Chart(ctx, {
                    type: 'line',
                    data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true, ticks: { precision: 0 } },
                        },
                        interaction: { mode: 'index', intersect: false },
                    }
                });
            }

            function init() {
                if (typeof Chart === 'undefined') return;

                const chartEl = document.getElementById('fvAdminHomeTotalPermitsChart');
                if (!chartEl) return;

                const pieEl = document.getElementById('fvAdminHomePermitsPieChart');
                if (!pieEl) return;

                window.__fvAdminHomeCharts = window.__fvAdminHomeCharts || {};

                const data = (chartsData && chartsData[initialRange]) ? chartsData[initialRange] : { labels: [], datasets: [] };
                window.__fvAdminHomeCharts.total = buildLine(chartEl.getContext('2d'), data);

                const pieInit = (pieData && pieData[initialPieRange]) ? pieData[initialPieRange] : { labels: [], datasets: [] };
                window.__fvAdminHomeCharts.pie = new Chart(pieEl.getContext('2d'), {
                    type: 'pie',
                    data: pieInit,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                    }
                });
            }

            function updateCharts(charts, range) {
                if (!charts || !window.__fvAdminHomeCharts || !window.__fvAdminHomeCharts.total) return;
                if (!range || !charts[range]) return;

                window.__fvAdminHomeCharts.total.data = charts[range];
                window.__fvAdminHomeCharts.total.update();
            }

            function updatePie(pie, pieRange) {
                if (!pie || !window.__fvAdminHomeCharts || !window.__fvAdminHomeCharts.pie) return;
                if (!pieRange || !pie[pieRange]) return;

                window.__fvAdminHomeCharts.pie.data = pie[pieRange];
                window.__fvAdminHomeCharts.pie.update();
            }

            window.addEventListener('adminHomeDashboardUpdated', (event) => {
                if (!event.detail) return;

                if (event.detail.charts && event.detail.range) {
                    updateCharts(event.detail.charts, event.detail.range);
                }

                // Only update pie if data is present (not null); range changes send null to skip
                if (event.detail.pie && event.detail.pieRange) {
                    updatePie(event.detail.pie, event.detail.pieRange);
                }
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
</div>
