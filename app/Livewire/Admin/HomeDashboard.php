<?php

namespace App\Livewire\Admin;

use App\Models\Location;
use App\Models\Permit;
use App\Models\User;
use Carbon\Carbon;
use Carbon\Constants\UnitValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class HomeDashboard extends Component
{
    public string $range = 'week';

    public string $pieRange = 'year';

    public string $calendarMonth;

    public ?string $selectedDay = null;

    public array $cards = [];

    public array $charts = [];

    public array $pie = [];

    public array $calendar = [];

    // Status labels & colors matching Permit constants
    protected const STATUS_META = [
        Permit::STATUS_SCHEDULED   => ['label' => 'Scheduled',   'border' => '#3b82f6', 'bg' => 'rgba(59,130,246,0.10)',   'point' => '#3b82f6'],
        Permit::STATUS_IN_PROGRESS => ['label' => 'In Progress', 'border' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.10)',   'point' => '#f59e0b'],
        Permit::STATUS_COMPLETED   => ['label' => 'Completed',   'border' => '#10b981', 'bg' => 'rgba(16,185,129,0.10)',   'point' => '#10b981'],
        Permit::STATUS_CANCELLED   => ['label' => 'Cancelled',   'border' => '#ef4444', 'bg' => 'rgba(239,68,68,0.10)',    'point' => '#ef4444'],
        Permit::STATUS_ON_HOLD     => ['label' => 'On Hold',     'border' => '#8b5cf6', 'bg' => 'rgba(139,92,246,0.10)',   'point' => '#8b5cf6'],
        Permit::STATUS_RETURNED    => ['label' => 'Returned',    'border' => '#f97316', 'bg' => 'rgba(249,115,22,0.10)',   'point' => '#f97316'],
        Permit::STATUS_LAPSED      => ['label' => 'Lapsed',      'border' => '#6b7280', 'bg' => 'rgba(107,114,128,0.10)', 'point' => '#6b7280'],
        Permit::STATUS_RESOLVED    => ['label' => 'Resolved',    'border' => '#06b6d4', 'bg' => 'rgba(6,182,212,0.10)',   'point' => '#06b6d4'],
    ];

    public function mount(): void
    {
        $this->calendarMonth = now()->format('Y-m');
        $this->selectedDay   = now()->toDateString();
        $this->loadData();
    }

    public function setRange(string $range): void
    {
        $range = strtolower(trim($range));
        if (! in_array($range, ['week', 'month', 'year'], true)) {
            return;
        }

        $this->range = $range;
        $this->loadData();
        $this->dispatch('adminHomeDashboardUpdated', charts: $this->charts, pie: null, pieRange: null, cards: $this->cards, calendar: $this->calendar, range: $this->range);
    }

    public function setPieRange(string $range): void
    {
        $range = strtolower(trim($range));
        if (! in_array($range, ['year', 'overall'], true)) {
            return;
        }

        $this->pieRange = $range;
        $this->loadData();
        $this->dispatch('adminHomeDashboardUpdated', charts: $this->charts, pie: $this->pie, pieRange: $this->pieRange, cards: $this->cards, calendar: $this->calendar, range: $this->range);
    }

    public function setCalendarMonth(string $ym): void
    {
        $ym = trim($ym);
        if (! preg_match('/^\d{4}-\d{2}$/', $ym)) {
            return;
        }

        $this->calendarMonth = $ym;
        $this->selectedDay   = $ym === now()->format('Y-m') ? now()->toDateString() : null;
        $this->loadCalendar();
        $this->dispatch('adminHomeDashboardCalendarUpdated', calendar: $this->calendar);
    }

    public function selectDay(string $day): void
    {
        $day = trim($day);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
            return;
        }

        $this->selectedDay = $day;

        $selectedMonth = Carbon::parse($day)->format('Y-m');
        if ($selectedMonth !== $this->calendarMonth) {
            $this->calendarMonth = $selectedMonth;
        }

        $this->loadCalendar();
        $this->dispatch('adminHomeDashboardCalendarUpdated', calendar: $this->calendar);
    }

    public function refreshDashboard(): void
    {
        $this->loadData();
        $this->dispatch('adminHomeDashboardUpdated', charts: $this->charts, pie: $this->pie, pieRange: $this->pieRange, cards: $this->cards, calendar: $this->calendar, range: $this->range);
    }

    // -------------------------------------------------------------------------
    // Department scope
    // -------------------------------------------------------------------------

    protected function departmentScope(Builder $query): Builder
    {
        $user = auth()->user();
        $dept = $user?->department;

        if (
            $user?->user_type === 2 || // Super Admin
            in_array($dept, ['PURCHASING', 'IT & SECURITY'], true)
        ) {
            return $query; // sees everything
        }

        return $query->where('permits.department', $dept);
    }

    protected function permitsQuery(): Builder
    {
        return $this->departmentScope(Permit::query());
    }

    // -------------------------------------------------------------------------
    // Load
    // -------------------------------------------------------------------------

    protected function loadData(): void
    {
        $now        = now();
        $weekStart  = $now->copy()->startOfWeek(UnitValue::SUNDAY);
        $weekEnd    = $weekStart->copy()->endOfWeek(UnitValue::SATURDAY);
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();
        $yearStart  = $now->copy()->startOfYear();
        $yearEnd    = $now->copy()->endOfYear();

        [$start, $end] = match ($this->range) {
            'month' => [$monthStart, $monthEnd],
            'year'  => [$yearStart, $yearEnd],
            default => [$weekStart, $weekEnd],
        };

        $totalPermits = $this->permitsQuery()
            ->whereBetween('date_of_visit', [$start, $end])
            ->count();

        $locationsCount = Location::query()->count();
        $usersCount     = $this->baseUsersQuery()->count();

        $this->cards = [
            'totals' => [
                'users'     => $usersCount,
                'locations' => $locationsCount,
                'permits'   => $totalPermits,
            ],
        ];

        $this->charts = [
            'week'  => $this->weekStatusChart($weekStart, $weekEnd),
            'month' => $this->monthStatusChart($monthStart, $monthEnd, (int) $now->daysInMonth),
            'year'  => $this->yearStatusChart($yearStart, $yearEnd),
        ];

        $this->pie = [
            'year'    => $this->statusPieChart($yearStart, $yearEnd),
            'overall' => $this->statusPieChart(null, null),
        ];

        $this->calendar = $this->buildCalendar($this->calendarMonth, $this->selectedDay);
    }

    protected function loadCalendar(): void
    {
        $this->calendar = $this->buildCalendar($this->calendarMonth, $this->selectedDay);
    }

    // -------------------------------------------------------------------------
    // Pie chart
    // -------------------------------------------------------------------------

    protected function statusPieChart(?Carbon $start, ?Carbon $end): array
    {
        $query = $this->permitsQuery()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status');

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        $rows = $query->get()->keyBy('status');

        $labels     = [];
        $data       = [];
        $bgColors   = [];
        $borders    = [];

        foreach (self::STATUS_META as $statusCode => $meta) {
            $labels[]   = $meta['label'];
            $data[]     = (int) ($rows[$statusCode]->total ?? 0);
            $bgColors[] = str_replace('0.10', '0.70', $meta['bg']);
            $borders[]  = $meta['border'];
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                [
                    'data'            => $data,
                    'backgroundColor' => $bgColors,
                    'borderColor'     => $borders,
                    'borderWidth'     => 1,
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Line charts
    // -------------------------------------------------------------------------

    protected function buildStatusLineDatasets(array $statusCounts, int $points, Carbon $start): array
    {
        $datasets = [];

        foreach (self::STATUS_META as $statusCode => $meta) {
            $pointData = [];
            for ($i = 0; $i < $points; $i++) {
                $key         = $start->copy()->addDays($i)->toDateString() . '_' . $statusCode;
                $pointData[] = (int) ($statusCounts[$key] ?? 0);
            }

            $datasets[] = [
                'label'            => $meta['label'],
                'data'             => $pointData,
                'borderColor'      => $meta['border'],
                'backgroundColor'  => $meta['bg'],
                'tension'          => 0.35,
                'fill'             => false,
                'borderWidth'      => 3,
                'pointBackgroundColor' => $meta['point'],
                'pointBorderColor'     => '#ffffff',
                'pointBorderWidth'     => 2,
            ];
        }

        return $datasets;
    }

    protected function buildStatusMonthDatasets(array $statusCounts, int $daysInMonth, Carbon $start): array
    {
        return $this->buildStatusLineDatasets($statusCounts, $daysInMonth, $start);
    }

    protected function countsByDayAndStatus(Carbon $start, Carbon $end): array
    {
        $rows = $this->permitsQuery()
            ->selectRaw('DATE(date_of_visit) as day, status, COUNT(*) as total')
            ->whereBetween('date_of_visit', [$start, $end])
            ->groupBy('day', 'status')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[$row->day . '_' . $row->status] = (int) $row->total;
        }

        return $out;
    }

    protected function countsByMonthAndStatus(Carbon $start, Carbon $end): array
    {
        $rows = $this->permitsQuery()
            ->selectRaw('MONTH(date_of_visit) as m, status, COUNT(*) as total')
            ->whereBetween('date_of_visit', [$start, $end])
            ->groupBy('m', 'status')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[$row->m . '_' . $row->status] = (int) $row->total;
        }

        return $out;
    }

    protected function weekStatusChart(Carbon $start, Carbon $end): array
    {
        $counts = $this->countsByDayAndStatus($start, $end);

        return [
            'labels'   => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            'datasets' => $this->buildStatusLineDatasets($counts, 7, $start),
        ];
    }

    protected function monthStatusChart(Carbon $start, Carbon $end, int $daysInMonth): array
    {
        $counts = $this->countsByDayAndStatus($start, $end);
        $labels = array_map('strval', range(1, $daysInMonth));

        return [
            'labels'   => $labels,
            'datasets' => $this->buildStatusMonthDatasets($counts, $daysInMonth, $start),
        ];
    }

    protected function yearStatusChart(Carbon $start, Carbon $end): array
    {
        $rows = $this->permitsQuery()
            ->selectRaw('MONTH(date_of_visit) as m, status, COUNT(*) as total')
            ->whereBetween('date_of_visit', [$start, $end])
            ->groupBy('m', 'status')
            ->get();

        $raw = [];
        foreach ($rows as $row) {
            $raw[$row->m . '_' . $row->status] = (int) $row->total;
        }

        $datasets = [];
        foreach (self::STATUS_META as $statusCode => $meta) {
            $data = [];
            for ($m = 1; $m <= 12; $m++) {
                $data[] = (int) ($raw[$m . '_' . $statusCode] ?? 0);
            }
            $datasets[] = [
                'label'               => $meta['label'],
                'data'                => $data,
                'borderColor'         => $meta['border'],
                'backgroundColor'     => $meta['bg'],
                'tension'             => 0.35,
                'fill'                => false,
                'borderWidth'         => 3,
                'pointBackgroundColor' => $meta['point'],
                'pointBorderColor'    => '#ffffff',
                'pointBorderWidth'    => 2,
            ];
        }

        return [
            'labels'   => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => $datasets,
        ];
    }

    // -------------------------------------------------------------------------
    // Calendar
    // -------------------------------------------------------------------------

    protected function buildCalendar(string $ym, ?string $selectedDay): array
    {
        $monthStart = Carbon::createFromFormat('Y-m', $ym)->startOfMonth()->startOfDay();
        $monthEnd   = $monthStart->copy()->endOfMonth()->endOfDay();

        $counts = $this->countsByDayForCalendar($monthStart, $monthEnd);

        $gridStart = $monthStart->copy()->startOfWeek(UnitValue::SUNDAY);
        $gridEnd   = $monthEnd->copy()->endOfWeek(UnitValue::SATURDAY);

        $days = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $date      = $d->toDateString();
            $inMonth   = $d->format('Y-m') === $ym;
            $dayCounts = $counts[$date] ?? $this->emptyDayCounts();

            $days[] = [
                'date'        => $date,
                'day'         => (int) $d->format('j'),
                'in_month'    => $inMonth,
                'is_today'    => $date === now()->toDateString(),
                'is_selected' => $selectedDay !== null && $date === $selectedDay,
                'counts'      => $dayCounts,
            ];
        }

        $selected = null;
        if ($selectedDay !== null) {
            $selected = [
                'date'   => $selectedDay,
                'counts' => $counts[$selectedDay] ?? $this->emptyDayCounts(),
            ];
        }

        return [
            'month'       => $ym,
            'month_label' => $monthStart->format('F Y'),
            'grid'        => $days,
            'selected'    => $selected,
        ];
    }

    protected function emptyDayCounts(): array
    {
        $counts = ['total' => 0];
        foreach (self::STATUS_META as $code => $meta) {
            $counts[$code] = 0;
        }

        return $counts;
    }

    protected function countsByDayForCalendar(Carbon $start, Carbon $end): array
    {
        $rows = $this->permitsQuery()
            ->selectRaw('DATE(date_of_visit) as day, status, COUNT(*) as total')
            ->whereBetween('date_of_visit', [$start, $end])
            ->groupBy('day', 'status')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $day = (string) $row->day;
            if (! isset($out[$day])) {
                $out[$day] = $this->emptyDayCounts();
            }
            $out[$day][(int) $row->status] = (int) $row->total;
            $out[$day]['total'] += (int) $row->total;
        }

        return $out;
    }

    // -------------------------------------------------------------------------
    // Misc helpers
    // -------------------------------------------------------------------------

    protected function baseUsersQuery()
    {
        return User::query()->where('user_type', 0);
    }

    protected function countsByDayForModel($query, Carbon $start, Carbon $end): array
    {
        $rows = $query
            ->selectRaw('DATE(created_at) as day')
            ->addSelect(DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row->day] = (int) $row->total;
        }

        return $out;
    }

    public function render()
    {
        return view('livewire.admin.home-dashboard');
    }
}