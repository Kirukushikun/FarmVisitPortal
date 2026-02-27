<?php

namespace App\Livewire\Admin;

use App\Models\Location;
use App\Models\Permit;
use App\Models\User;
use Carbon\Carbon;
use Carbon\Constants\UnitValue;
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

    public function mount(): void
    {
        $this->calendarMonth = now()->format('Y-m');
        $this->selectedDay = now()->toDateString();
        $this->loadData();
    }

    public function setRange(string $range): void
    {
        $range = strtolower(trim($range));
        if (!in_array($range, ['week', 'month', 'year'], true)) {
            return;
        }

        $this->range = $range;
        $this->loadData();
        $this->dispatch('adminHomeDashboardUpdated', charts: $this->charts, pie: $this->pie, pieRange: $this->pieRange, cards: $this->cards, calendar: $this->calendar, range: $this->range);
    }

    public function setPieRange(string $range): void
    {
        $range = strtolower(trim($range));
        if (!in_array($range, ['year', 'overall'], true)) {
            return;
        }

        $this->pieRange = $range;
        $this->loadData();
        $this->dispatch('adminHomeDashboardUpdated', charts: $this->charts, pie: $this->pie, pieRange: $this->pieRange, cards: $this->cards, calendar: $this->calendar, range: $this->range);
    }

    public function setCalendarMonth(string $ym): void
    {
        $ym = trim($ym);
        if (!preg_match('/^\d{4}-\d{2}$/', $ym)) {
            return;
        }

        $this->calendarMonth = $ym;
        $this->selectedDay = $ym === now()->format('Y-m') ? now()->toDateString() : null;
        $this->loadData();
        $this->dispatch('adminHomeDashboardUpdated', charts: $this->charts, pie: $this->pie, pieRange: $this->pieRange, cards: $this->cards, calendar: $this->calendar, range: $this->range);
    }

    public function selectDay(string $day): void
    {
        $day = trim($day);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
            return;
        }

        $this->selectedDay = $day;
        $this->calendar = $this->buildCalendar($this->calendarMonth, $this->selectedDay);
    }

    public function refreshDashboard(): void
    {
        $this->loadData();
        $this->dispatch('adminHomeDashboardUpdated', charts: $this->charts, pie: $this->pie, pieRange: $this->pieRange, cards: $this->cards, calendar: $this->calendar, range: $this->range);
    }

    protected function loadData(): void
    {
        $now = now();

        $weekStart = $now->copy()->startOfWeek(UnitValue::SUNDAY)->startOfDay();
        $weekEnd = $now->copy()->endOfWeek(UnitValue::SATURDAY)->endOfDay();

        $monthStart = $now->copy()->startOfMonth()->startOfDay();
        $monthEnd = $now->copy()->endOfMonth()->endOfDay();

        $yearStart = $now->copy()->startOfYear()->startOfDay();
        $yearEnd = $now->copy()->endOfYear()->endOfDay();

        [$start, $end] = match ($this->range) {
            'month' => [$monthStart, $monthEnd],
            'year' => [$yearStart, $yearEnd],
            default => [$weekStart, $weekEnd],
        };

        $totalPermits = Permit::query()
            ->whereBetween('date_of_visit', [$start, $end])
            ->count();

        $this->cards = [
            'totals' => [
                'users' => User::query()->count(),
                'locations' => Location::query()->count(),
                'permits' => $totalPermits,
            ],
        ];

        $this->charts = [
            'week' => $this->weekNewEntitiesChart($weekStart, $weekEnd),
            'month' => $this->monthNewEntitiesChart($monthStart, $monthEnd, (int) $now->daysInMonth),
            'year' => $this->yearNewEntitiesChart($yearStart, $yearEnd),
        ];

        $this->pie = [
            'year' => $this->permitStatusPieChart($yearStart, $yearEnd),
            'overall' => $this->permitStatusPieChart(null, null),
        ];

        $this->calendar = $this->buildCalendar($this->calendarMonth, $this->selectedDay);
    }

    protected function permitStatusPieChart(?Carbon $start, ?Carbon $end): array
    {
        $query = Permit::query();
        if ($start !== null && $end !== null) {
            $query->whereBetween('date_of_visit', [$start, $end]);
        }

        $row = $query
            ->selectRaw('COUNT(*) as total')
            ->addSelect(DB::raw('SUM(CASE WHEN date_of_visit > CURRENT_DATE THEN 1 ELSE 0 END) as scheduled'))
            ->addSelect(DB::raw('SUM(CASE WHEN DATE(date_of_visit) = CURRENT_DATE AND received_by IS NULL AND status != 3 THEN 1 ELSE 0 END) as in_progress'))
            ->addSelect(DB::raw('SUM(CASE WHEN date_of_visit <= CURRENT_DATE AND received_by IS NOT NULL THEN 1 ELSE 0 END) as received'))
            ->addSelect(DB::raw('SUM(CASE WHEN (date_of_visit < CURRENT_DATE AND received_by IS NULL) OR (DATE(date_of_visit) = CURRENT_DATE AND status = 3) THEN 1 ELSE 0 END) as cancelled'))
            ->first();

        $scheduled = (int) ($row->scheduled ?? 0);
        $inProgress = (int) ($row->in_progress ?? 0);
        $received = (int) ($row->received ?? 0);
        $cancelled = (int) ($row->cancelled ?? 0);

        return [
            'labels' => ['Scheduled', 'In Progress', 'Received', 'Cancelled'],
            'datasets' => [
                [
                    'data' => [$scheduled, $inProgress, $received, $cancelled],
                    'backgroundColor' => [
                        'rgba(249,115,22,0.70)',
                        'rgba(59,130,246,0.70)',
                        'rgba(16,185,129,0.70)',
                        'rgba(239,68,68,0.70)',
                    ],
                    'borderColor' => ['#f97316', '#3b82f6', '#10b981', '#ef4444'],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function weekNewEntitiesChart(Carbon $start, Carbon $end): array
    {
        $labels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        $permits = $this->countsByDayForModel(Permit::query(), $start, $end);
        $users = $this->countsByDayForModel(User::query(), $start, $end);
        $locations = $this->countsByDayForModel(Location::query(), $start, $end);

        $permitsData = [];
        $usersData = [];
        $locationsData = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $start->copy()->addDays($i)->toDateString();
            $permitsData[] = (int) ($permits[$day] ?? 0);
            $usersData[] = (int) ($users[$day] ?? 0);
            $locationsData[] = (int) ($locations[$day] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'New Permits',
                    'data' => $permitsData,
                    'borderColor' => '#f97316',
                    'backgroundColor' => 'rgba(249,115,22,0.10)',
                    'tension' => 0.35,
                    'fill' => false,
                    'borderWidth' => 3,
                    'pointBackgroundColor' => '#f97316',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                ],
                [
                    'label' => 'New Users',
                    'data' => $usersData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59,130,246,0.10)',
                    'tension' => 0.35,
                    'fill' => false,
                    'borderWidth' => 3,
                    'pointBackgroundColor' => '#3b82f6',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                ],
                [
                    'label' => 'New Locations',
                    'data' => $locationsData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16,185,129,0.10)',
                    'tension' => 0.35,
                    'fill' => false,
                    'borderWidth' => 3,
                    'pointBackgroundColor' => '#10b981',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                ],
            ],
        ];
    }

    protected function monthNewEntitiesChart(Carbon $start, Carbon $end, int $daysInMonth): array
    {
        $labels = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $labels[] = (string) $d;
        }

        $permits = $this->countsByDayForModel(Permit::query(), $start, $end);
        $users = $this->countsByDayForModel(User::query(), $start, $end);
        $locations = $this->countsByDayForModel(Location::query(), $start, $end);

        $permitsData = [];
        $usersData = [];
        $locationsData = [];
        for ($i = 0; $i < $daysInMonth; $i++) {
            $day = $start->copy()->addDays($i)->toDateString();
            $permitsData[] = (int) ($permits[$day] ?? 0);
            $usersData[] = (int) ($users[$day] ?? 0);
            $locationsData[] = (int) ($locations[$day] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'New Permits',
                    'data' => $permitsData,
                    'borderColor' => '#f97316',
                    'backgroundColor' => 'rgba(249,115,22,0.10)',
                    'tension' => 0.35,
                    'fill' => false,
                    'borderWidth' => 3,
                    'pointBackgroundColor' => '#f97316',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                ],
                [
                    'label' => 'New Users',
                    'data' => $usersData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59,130,246,0.10)',
                    'tension' => 0.35,
                    'fill' => false,
                    'borderWidth' => 3,
                    'pointBackgroundColor' => '#3b82f6',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                ],
                [
                    'label' => 'New Locations',
                    'data' => $locationsData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16,185,129,0.10)',
                    'tension' => 0.35,
                    'fill' => false,
                    'borderWidth' => 3,
                    'pointBackgroundColor' => '#10b981',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                ],
            ],
        ];
    }

    protected function yearNewEntitiesChart(Carbon $start, Carbon $end): array
    {
        $permits = $this->countsByMonthForModel(Permit::query(), $start, $end);
        $users = $this->countsByMonthForModel(User::query(), $start, $end);
        $locations = $this->countsByMonthForModel(Location::query(), $start, $end);

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $permitsData = [];
        $usersData = [];
        $locationsData = [];
        for ($m = 1; $m <= 12; $m++) {
            $permitsData[] = (int) ($permits[$m] ?? 0);
            $usersData[] = (int) ($users[$m] ?? 0);
            $locationsData[] = (int) ($locations[$m] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'New Permits',
                    'data' => $permitsData,
                    'borderColor' => '#f97316',
                    'backgroundColor' => 'rgba(249,115,22,0.10)',
                    'tension' => 0.35,
                    'fill' => false,
                    'borderWidth' => 3,
                    'pointBackgroundColor' => '#f97316',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                ],
                [
                    'label' => 'New Users',
                    'data' => $usersData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59,130,246,0.10)',
                    'tension' => 0.35,
                    'fill' => false,
                    'borderWidth' => 3,
                    'pointBackgroundColor' => '#3b82f6',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                ],
                [
                    'label' => 'New Locations',
                    'data' => $locationsData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16,185,129,0.10)',
                    'tension' => 0.35,
                    'fill' => false,
                    'borderWidth' => 3,
                    'pointBackgroundColor' => '#10b981',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                ],
            ],
        ];
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

    protected function countsByMonthForModel($query, Carbon $start, Carbon $end): array
    {
        $rows = $query
            ->selectRaw('MONTH(created_at) as m')
            ->addSelect(DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('m')
            ->get();

        $out = array_fill(1, 12, 0);
        foreach ($rows as $row) {
            $out[(int) $row->m] = (int) $row->total;
        }

        return $out;
    }

    protected function buildCalendar(string $ym, ?string $selectedDay): array
    {
        $monthStart = Carbon::createFromFormat('Y-m', $ym)->startOfMonth()->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();

        $counts = $this->countsByDayForDerivedStatuses($monthStart, $monthEnd);

        $gridStart = $monthStart->copy()->startOfWeek(UnitValue::SUNDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(UnitValue::SATURDAY);

        $days = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $date = $d->toDateString();
            $inMonth = $d->format('Y-m') === $ym;
            $dayCounts = $counts[$date] ?? ['scheduled' => 0, 'in_progress' => 0, 'received' => 0, 'cancelled' => 0, 'total' => 0];

            $days[] = [
                'date' => $date,
                'day' => (int) $d->format('j'),
                'in_month' => $inMonth,
                'is_today' => $date === now()->toDateString(),
                'is_selected' => $selectedDay !== null && $date === $selectedDay,
                'counts' => $dayCounts,
            ];
        }

        $selected = null;
        if ($selectedDay !== null) {
            $selected = [
                'date' => $selectedDay,
                'counts' => $counts[$selectedDay] ?? ['scheduled' => 0, 'in_progress' => 0, 'received' => 0, 'cancelled' => 0, 'total' => 0],
            ];
        }

        return [
            'month' => $ym,
            'month_label' => $monthStart->format('F Y'),
            'grid' => $days,
            'selected' => $selected,
        ];
    }

    protected function countsByDayForDerivedStatuses(Carbon $start, Carbon $end): array
    {
        $rows = Permit::query()
            ->selectRaw('DATE(date_of_visit) as day')
            ->addSelect(DB::raw('COUNT(*) as total'))
            ->addSelect(DB::raw('SUM(CASE WHEN date_of_visit > CURRENT_DATE THEN 1 ELSE 0 END) as scheduled'))
            ->addSelect(DB::raw('SUM(CASE WHEN DATE(date_of_visit) = CURRENT_DATE AND received_by IS NULL AND status != 3 THEN 1 ELSE 0 END) as in_progress'))
            ->addSelect(DB::raw('SUM(CASE WHEN date_of_visit <= CURRENT_DATE AND received_by IS NOT NULL THEN 1 ELSE 0 END) as received'))
            ->addSelect(DB::raw('SUM(CASE WHEN (date_of_visit < CURRENT_DATE AND received_by IS NULL) OR (DATE(date_of_visit) = CURRENT_DATE AND status = 3) THEN 1 ELSE 0 END) as cancelled'))
            ->whereBetween('date_of_visit', [$start, $end])
            ->groupBy('day')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $day = (string) $row->day;
            $out[$day] = [
                'total' => (int) $row->total,
                'scheduled' => (int) $row->scheduled,
                'in_progress' => (int) $row->in_progress,
                'received' => (int) $row->received,
                'cancelled' => (int) $row->cancelled,
            ];
        }

        return $out;
    }

    public function render()
    {
        return view('livewire.admin.home-dashboard');
    }
}
