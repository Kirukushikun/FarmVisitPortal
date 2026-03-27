<x-layout>
    <x-navbar :breadcrumbs="[
        ['label' => 'Dashboard', 'href' => route('user.home')],
        ['label' => 'View Permit'],
    ]" :includeSidebar="true" :user="Auth::user()">
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <div class="p-4 space-y-4">
                @php
                    // ----------------------------------------------------------------
                    // Helpers
                    // ----------------------------------------------------------------
                    $printDuration = function (null|int|float|string $hours): string {
                        if ($hours === null) return '';
                        $hoursFloat   = max(0, (float) $hours);
                        $totalSeconds = (int) round($hoursFloat * 3600);
                        return sprintf('%02d:%02d:%02d',
                            intdiv($totalSeconds, 3600),
                            intdiv($totalSeconds % 3600, 60),
                            $totalSeconds % 60
                        );
                    };

                    $displayVal = function (mixed $value): string {
                        if (is_array($value) || is_object($value)) return 'N/A';
                        $value = is_string($value) ? trim($value) : $value;
                        if ($value === null || $value === '') return 'N/A';
                        return (string) $value;
                    };

                    // ----------------------------------------------------------------
                    // Permit data
                    // ----------------------------------------------------------------
                    $dateFilled       = $permit->created_at?->format('F j, Y') ?? '';
                    $farm             = $permit->farmLocation?->name ?? '';
                    $farmType         = (int) ($permit->farmLocation?->farm_type ?? 0);
                    $areaName         = $permit->area?->name ?? '';
                    $dateOfVisit      = $permit->date_of_visit?->format('F j, Y') ?? '';
                    $expectedDuration = $printDuration($permit->expected_duration_hours);
                    $previousFarm     = $permit->previous_farm_location ?? '';
                    $previousFarmDate = $permit->date_of_visit_previous_farm?->format('F j, Y') ?? '';

                    // Names
                    $rawNames  = $permit->names;
                    $namesData = null;
                    if (is_array($rawNames) && isset($rawNames['mode'])) {
                        $namesData = $rawNames;
                    } elseif (is_string($rawNames) && trim($rawNames) !== '') {
                        $decoded = json_decode($rawNames, true);
                        if (is_array($decoded) && isset($decoded['mode'])) {
                            $namesData = $decoded;
                        }
                    }

                    $isDetailedMode = $namesData && $namesData['mode'] === 'detailed';
                    $isSimpleMode   = $namesData && $namesData['mode'] === 'simple';
                    $groups         = $isDetailedMode ? ($namesData['groups'] ?? []) : [];

                    // ----------------------------------------------------------------
                    // Red alert — computed from data only, not from permit->red_alert
                    // permit->red_alert is the guard-toggled override stored on the record
                    // ----------------------------------------------------------------
                    $requiredDays     = $farmType === 1 ? 3 : 5;
                    $alertGroupIdxs   = [];
                    $computedRedAlert = false;

                    if ($isDetailedMode) {
                        foreach ($groups as $i => $group) {
                            $dv = $group['date_visited'] ?? '';
                            if ($dv === '') continue;
                            $diff = \Carbon\Carbon::parse($dv)->startOfDay()
                                ->diffInDays(\Carbon\Carbon::parse($permit->date_of_visit)->startOfDay());
                            if ($diff < $requiredDays) {
                                $alertGroupIdxs[] = $i;
                                $computedRedAlert  = true;
                            }
                        }
                    } elseif ($isSimpleMode && $permit->date_of_visit_previous_farm) {
                        $diff = $permit->date_of_visit_previous_farm->startOfDay()
                            ->diffInDays(\Carbon\Carbon::parse($permit->date_of_visit)->startOfDay());
                        if ($diff < $requiredDays) {
                            $computedRedAlert = true;
                        }
                    }

                    // Respect guard-toggled red_alert from permit record too
                    $hasRedAlert = $computedRedAlert || (bool) ($permit->red_alert ?? false);

                    // Build a single, specific red alert message
                    $redAlertMessage = '';
                    if ($hasRedAlert) {
                        if ($isDetailedMode && count($alertGroupIdxs) > 0) {
                            $lines = [];
                            foreach ($alertGroupIdxs as $idx) {
                                $origin = $groups[$idx]['origin'] ?? 'Group ' . ($idx + 1);
                                $names  = implode(', ', array_filter(array_map('trim', explode("\n", $groups[$idx]['names'] ?? ''))));
                                $lines[] = $origin . ': ' . $names;
                            }
                            $redAlertMessage = 'The following groups have not met the required ' . $requiredDays . '-day interval — ' . implode(' | ', $lines);
                        } else {
                            $redAlertMessage = 'Visitors have not met the required ' . $requiredDays . '-day interval since their last farm visit.';
                        }
                    }

                    // ----------------------------------------------------------------
                    // Viewer + status context
                    // ----------------------------------------------------------------
                    $viewer                  = Auth::user();
                    $isAdmin                 = in_array((int) ($viewer->user_type ?? 0), [1, 2], true);
                    $isAcceptedByCurrentUser = (int) ($permit->received_by ?? 0) === (int) ($viewer->id ?? 0);
                    $currentStatus           = (int) ($permit->status ?? 0);

                    $lastAdminLog = $permit->logs->whereIn('action', [3, 4, 5, 9])->sortByDesc('created_at')->first();
                    $lastHoldLog  = $permit->logs->where('action', 2)->sortByDesc('created_at')->first();
                    $wasEverHeld  = $permit->logs->whereIn('action', [2])->count() > 0;
                    $adminApproved = $lastAdminLog && in_array((int) $lastAdminLog->action, [3, 9], true);
                    $adminRejected = $lastAdminLog && (int) $lastAdminLog->action === 4;

                    // Button visibility
                    $showCompleteOnly = $currentStatus === 1 && $wasEverHeld && $adminApproved;
                    $showAllActions   = $currentStatus === 1 && ! $showCompleteOnly;
                    $showAnyButtons   = $showCompleteOnly || $showAllActions;

                    // Zone 2 banner type — one signal only
                    $bannerType = null;
                    if ($currentStatus === 1 && $adminApproved)       $bannerType = 'approved';
                    elseif ($currentStatus === 4)                      $bannerType = 'on_hold';
                    elseif ($currentStatus === 3 && $adminRejected)    $bannerType = 'rejected';
                    elseif ($currentStatus === 5)                      $bannerType = 'returned';
                    // Red alert without any of the above statuses gets its own banner
                    $showRedAlertBanner = $hasRedAlert && $bannerType === null && $currentStatus === 1 && ! $adminApproved;
                @endphp

                <style>
                    @media print {
                        .no-print { display: none !important; }
                        body * { visibility: hidden !important; }
                        .print-page, .print-page * { visibility: visible !important; }
                        .print-page { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; box-shadow: none !important; border-radius: 0 !important; border: none !important; }
                        .print-wrap { padding: 0 !important; }
                        .print-bg { background: white !important; }
                        .text-gray-900, .text-gray-900 * { color: #111827 !important; }
                        .text-gray-700, .text-gray-700 * { color: #374151 !important; }
                        .dark\:text-white, .dark\:text-white * { color: #111827 !important; }
                        .dark\:text-gray-200, .dark\:text-gray-200 * { color: #374151 !important; }
                        .dark\:bg-gray-800 { background: white !important; }
                        .dark\:bg-gray-900 { background: white !important; }
                        .dark\:border-gray-700 { border-color: #e5e7eb !important; }
                    }
                </style>

                {{-- ================================================================
                     ZONE 1 — PERMIT DOCUMENT (universal, print-ready)
                     ================================================================ --}}
                <div class="w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 px-8 pt-6 pb-6 mx-auto print-page print-bg">
                    <div class="print-wrap">

                        {{-- MOBILE --}}
                        <div class="no-print md:hidden">
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                                <div class="flex justify-between items-center">
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                        FARM VISIT PERMIT {{ $permit->permit_id ?? '' }}
                                    </div>
                                    <a href="{{ route('user.home') }}" class="inline-flex items-center p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white cursor-pointer">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                        <span class="text-sm">Back</span>
                                    </a>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Date Filled: {{ $displayVal($dateFilled) }}</div>
                                <div class="mt-4 space-y-3 text-sm">
                                    <div><div class="font-semibold text-gray-700 dark:text-gray-200">Area</div><div class="text-gray-900 dark:text-white">{{ $displayVal($areaName) }}</div></div>
                                    <div><div class="font-semibold text-gray-700 dark:text-gray-200">Farm</div><div class="text-gray-900 dark:text-white">{{ $displayVal($farm) }}</div></div>
                                    <div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Visitor Names</div>
                                        @if ($isDetailedMode)
                                            <div class="space-y-1 mt-1">
                                                @foreach ($groups as $i => $group)
                                                    <div class="{{ in_array($i, $alertGroupIdxs) ? 'text-red-600 dark:text-red-400 font-medium' : '' }}">
                                                        <span class="font-semibold">{{ $group['origin'] }}:</span>
                                                        {{ implode(', ', array_filter(array_map('trim', explode("\n", $group['names'])))) }}
                                                        @if (in_array($i, $alertGroupIdxs)) 🚨 @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif ($isSimpleMode)
                                            <div class="text-gray-900 dark:text-white whitespace-pre-line">{{ $namesData['value'] }}</div>
                                        @else
                                            <div class="text-gray-900 dark:text-white">N/A</div>
                                        @endif
                                    </div>
                                    <div><div class="font-semibold text-gray-700 dark:text-gray-200">Date of Visit</div><div class="text-gray-900 dark:text-white">{{ $displayVal($dateOfVisit) }}</div></div>
                                    <div><div class="font-semibold text-gray-700 dark:text-gray-200">Expected Duration</div><div class="text-gray-900 dark:text-white">{{ $displayVal($expectedDuration) }}</div></div>
                                    <div><div class="font-semibold text-gray-700 dark:text-gray-200">Purpose</div><div class="text-gray-900 dark:text-white">{{ $displayVal($permit->purpose ?? null) }}</div></div>
                                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                        <div class="font-semibold text-gray-700 dark:text-gray-200 mb-1">Farm Travel History</div>
                                        @if ($isDetailedMode)
                                            @foreach ($groups as $i => $group)
                                                @if (!empty($group['previous_farm']) || !empty($group['date_visited']))
                                                    <div class="mb-2 p-2 rounded border {{ in_array($i, $alertGroupIdxs) ? 'border-red-300 dark:border-red-600 bg-red-50 dark:bg-red-900/20' : 'border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40' }}">
                                                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-0.5">Group {{ $i + 1 }}: {{ $group['origin'] }} @if(in_array($i, $alertGroupIdxs)) 🚨 @endif</div>
                                                        <div class="text-xs text-gray-900 dark:text-white"><span class="font-medium">Previous Farm:</span> {{ $displayVal($group['previous_farm'] ?? null) }}</div>
                                                        <div class="text-xs text-gray-900 dark:text-white"><span class="font-medium">Date Visited:</span> {{ !empty($group['date_visited']) ? \Carbon\Carbon::parse($group['date_visited'])->format('F j, Y') : 'N/A' }}</div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @else
                                            <div><div class="font-semibold text-gray-700 dark:text-gray-200">{{ $farmType === 1 ? 'Previous Poultry Farm Visited' : 'Previous Swine Farm Visited' }}</div><div class="text-gray-900 dark:text-white">{{ $displayVal($previousFarm) }}</div></div>
                                            <div class="mt-2"><div class="font-semibold text-gray-700 dark:text-gray-200">Previous Farm Date of Visit</div><div class="text-gray-900 dark:text-white">{{ $displayVal($previousFarmDate) }}</div></div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- DESKTOP / PRINT --}}
                        <div class="hidden md:block print:block">
                            <div class="flex justify-center mb-2">
                                <img src="{{ asset('images/BGC.png') }}" alt="BGC" class="h-20 w-48" />
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $displayVal($dateFilled) }}</div>
                                <a href="{{ route('user.home') }}" class="no-print inline-flex items-center p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white cursor-pointer">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                    <span class="text-sm">Back</span>
                                </a>
                            </div>
                            <div class="border-t border-b border-gray-900 dark:border-gray-300 py-2 text-center">
                                <div class="text-xl font-semibold tracking-wide italic text-gray-900 dark:text-gray-100" style="font-family: 'Times New Roman', Times, serif;">
                                    FARM VISIT PERMIT {{ $permit->permit_id ?? '' }}
                                </div>
                            </div>
                            <div class="mt-4">
                                <table class="w-full border border-gray-900 dark:border-gray-300" style="border-collapse: collapse; font-family: 'Times New Roman', Times, serif;">
                                    <colgroup><col style="width:24%"><col style="width:46%"><col style="width:15%"><col style="width:15%"></colgroup>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100"><span class="font-bold">AREA:</span> {{ $displayVal($areaName) }}</td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100"><span class="font-bold">FARM:</span> {{ $displayVal($farm) }}</td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100" colspan="2"><span class="font-bold">Date Filled:</span> {{ $displayVal($dateFilled) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100" colspan="2">
                                            <div class="font-bold">VISITOR NAMES:</div>
                                            @if ($isDetailedMode)
                                                <div class="space-y-1 mt-1">
                                                    @foreach ($groups as $i => $group)
                                                        <div class="{{ in_array($i, $alertGroupIdxs) ? 'text-red-700' : '' }}">
                                                            <span class="font-semibold">{{ $group['origin'] }}:</span>
                                                            {{ implode(', ', array_filter(array_map('trim', explode("\n", $group['names'])))) }}
                                                            @if (in_array($i, $alertGroupIdxs)) 🚨 @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @elseif ($isSimpleMode)
                                                <div style="white-space:pre-line">{{ $namesData['value'] }}</div>
                                            @else
                                                <div>N/A</div>
                                            @endif
                                        </td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100" colspan="2">
                                            <div class="font-bold">Area / Section / Department to Visit:</div>
                                            <div>{{ $displayVal($areaName) }}</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100"><span class="font-bold">DESTINATION</span></td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100">{{ $displayVal($farm) }}</td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top whitespace-nowrap text-gray-900 dark:text-gray-100"><span class="font-bold">DATE of VISIT</span></td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top whitespace-nowrap text-gray-900 dark:text-gray-100">{{ $displayVal($dateOfVisit) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 text-center text-gray-900 dark:text-gray-100" colspan="4"><span class="font-bold">Expected Duration:</span> {{ $displayVal($expectedDuration) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 text-center text-gray-900 dark:text-gray-100" colspan="4">
                                            <div class="font-bold">Farm Travel History</div>
                                            <div class="text-sm text-gray-700 dark:text-gray-300">{{ $farmType === 1 ? '(Must have not visited other Poultry Farm 3 days Prior to the Farm Visit)' : '(Must have not visited other Swine Farm 5 days Prior to the Farm Visit)' }}</div>
                                        </td>
                                    </tr>
                                    @if ($isDetailedMode)
                                        @foreach ($groups as $i => $group)
                                            <tr>
                                                <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100"><div class="font-bold text-center text-xs">{{ $group['origin'] }} @if(in_array($i,$alertGroupIdxs)) 🚨 @endif</div></td>
                                                <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100"><span class="font-semibold text-xs">{{ $farmType === 1 ? 'Prev. Poultry Farm:' : 'Prev. Swine Farm:' }}</span> {{ $displayVal($group['previous_farm'] ?? null) }}</td>
                                                <td class="border border-gray-900 dark:border-gray-300 p-2 align-top whitespace-nowrap text-gray-900 dark:text-gray-100"><div class="font-bold text-center">Date of Visit:</div></td>
                                                <td class="border border-gray-900 dark:border-gray-300 p-2 align-top whitespace-nowrap text-gray-900 dark:text-gray-100">{{ !empty($group['date_visited']) ? \Carbon\Carbon::parse($group['date_visited'])->format('F j, Y') : 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100"><div class="font-bold text-center">{{ $farmType === 1 ? 'Previous Poultry Farm Visited' : 'Previous Swine Farm Visited' }}</div></td>
                                            <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100">{{ $displayVal($previousFarm) }}</td>
                                            <td class="border border-gray-900 dark:border-gray-300 p-2 align-top whitespace-nowrap text-gray-900 dark:text-gray-100"><div class="font-bold text-center">Date of Visit:</div></td>
                                            <td class="border border-gray-900 dark:border-gray-300 p-2 align-top whitespace-nowrap text-gray-900 dark:text-gray-100">{{ $displayVal($previousFarmDate) }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100"><div class="font-bold text-center">PURPOSE</div></td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100" colspan="3">{{ $displayVal($permit->purpose ?? null) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ================================================================
                     ZONE 2 — STATUS BANNER
                     One signal. Red alert is folded in contextually.
                     Only renders when there is something notable to show.
                     ================================================================ --}}
                @if ($bannerType || $showRedAlertBanner)
                    @php
                        $bannerKey = $bannerType ?? 'red_alert';
                        $bannerConfig = match ($bannerKey) {
                            'approved'   => ['bg' => 'bg-green-600',  'border' => 'border-green-200 dark:border-green-700',  'icon' => 'check'],
                            'on_hold'    => ['bg' => 'bg-orange-500', 'border' => 'border-orange-200 dark:border-orange-700', 'icon' => 'warn'],
                            'rejected'   => ['bg' => 'bg-red-600',    'border' => 'border-red-200 dark:border-red-700',       'icon' => 'warn'],
                            'returned'   => ['bg' => 'bg-purple-600', 'border' => 'border-purple-200 dark:border-purple-700', 'icon' => 'arrow'],
                            'red_alert'  => ['bg' => 'bg-red-600',    'border' => 'border-red-200 dark:border-red-700',       'icon' => 'warn'],
                            default      => ['bg' => 'bg-gray-500',   'border' => 'border-gray-200 dark:border-gray-700',     'icon' => 'warn'],
                        };
                    @endphp
                    <div class="no-print w-full rounded-xl shadow-sm overflow-hidden border {{ $bannerConfig['border'] }} bg-white dark:bg-gray-800">
                        {{-- Colored header --}}
                        <div class="px-5 py-4 {{ $bannerConfig['bg'] }} text-white flex items-start gap-3">
                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if ($bannerConfig['icon'] === 'check')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @elseif ($bannerConfig['icon'] === 'arrow')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7m0 0l7-7m-7 7h18"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @endif
                            </svg>
                            <div class="flex-1">
                                <div class="font-bold text-sm leading-snug">
                                    @if ($bannerKey === 'approved')   ✅ Admin Approved — Let Them In
                                    @elseif ($bannerKey === 'on_hold') ⏸ On Hold — Awaiting Admin Decision
                                    @elseif ($bannerKey === 'rejected') 🚫 Entry Denied — Permit Rejected
                                    @elseif ($bannerKey === 'returned') 🔄 Returned for Correction
                                    @elseif ($bannerKey === 'red_alert') 🚨 Red Alert
                                    @endif
                                </div>
                                {{-- Fold red alert into banner subtitle when relevant --}}
                                @if ($hasRedAlert && ! in_array($bannerKey, ['approved']))
                                    <div class="text-xs opacity-90 mt-1">{{ $redAlertMessage }}</div>
                                @endif
                                @if ($bannerKey === 'rejected')
                                    <div class="text-xs opacity-90 mt-1">Visitors may contact Purchasing/Admin to clarify and request reconsideration.</div>
                                @endif
                                @if ($bannerKey === 'on_hold')
                                    <div class="text-xs opacity-90 mt-1">Please wait for admin to review and respond before taking further action.</div>
                                @endif
                            </div>
                        </div>
                        {{-- Body: show last admin message --}}
                        @if (in_array($bannerKey, ['approved', 'rejected', 'returned']) && $lastAdminLog?->message)
                            <div class="px-5 py-4 border-t {{ $bannerConfig['border'] }}">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Admin Note</div>
                                <div class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $lastAdminLog->message }}</div>
                            </div>
                        @endif
                        {{-- Body: show hold reason --}}
                        @if ($bannerKey === 'on_hold' && $lastHoldLog?->message)
                            <div class="px-5 py-4 border-t {{ $bannerConfig['border'] }}">
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Hold Reason</div>
                                <div class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $lastHoldLog->message }}</div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ================================================================
                     PHOTOS + REMARKS FORM
                     ================================================================ --}}
                @if (in_array($currentStatus, [1, 4]) || (($permit->photos ?? collect())->count() > 0))
                    <div class="no-print w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 px-6 pt-6 pb-4">
                        <livewire:permit-photo-upload :permit="$permit" :can-upload="in_array($currentStatus, [1, 4])" />
                        @if ($showAnyButtons)
                            <form id="completePermitForm" method="POST" action="{{ route('user.permits.complete', $permit) }}" class="mt-6">
                                @csrf
                                <x-text-area label="Remarks" name="remarks" placeholder="Enter remarks (optional)" :value="old('remarks', $permit->remarks ?? '')" />
                            </form>
                        @endif
                        @if (is_string($permit->remarks ?? null) && trim((string) $permit->remarks) !== '')
                            <div class="mt-4">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Remarks</div>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-gray-900 dark:text-white whitespace-pre-line">{{ trim((string) $permit->remarks) }}</div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ================================================================
                     PERMIT ACTIVITY TRAIL
                     ================================================================ --}}
                @if ($permit->logs->count() > 0)
                    <div class="no-print w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 px-6 py-6">
                        <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-4">Permit Activity</div>
                        <div class="space-y-3">
                            @foreach ($permit->logs as $log)
                                @php
                                    $lc = match ((int) $log->action) {
                                        0 => 'gray', 1 => 'blue', 2 => 'orange',
                                        3, 7, 9 => 'green', 4, 8 => 'red',
                                        5 => 'purple', 6 => 'blue', default => 'gray',
                                    };
                                    $lBg   = match ($lc) { 'green' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800', 'red' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800', 'orange' => 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800', 'blue' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800', 'purple' => 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800', default => 'bg-gray-50 dark:bg-gray-700/20 border-gray-200 dark:border-gray-700' };
                                    $lDot  = match ($lc) { 'green' => 'bg-green-500', 'red' => 'bg-red-500', 'orange' => 'bg-orange-500', 'blue' => 'bg-blue-500', 'purple' => 'bg-purple-500', default => 'bg-gray-400' };
                                    $lText = match ($lc) { 'green' => 'text-green-700 dark:text-green-400', 'red' => 'text-red-700 dark:text-red-400', 'orange' => 'text-orange-700 dark:text-orange-400', 'blue' => 'text-blue-700 dark:text-blue-400', 'purple' => 'text-purple-700 dark:text-purple-400', default => 'text-gray-600 dark:text-gray-400' };
                                @endphp
                                <div class="flex gap-3 items-start rounded-lg border px-4 py-3 {{ $lBg }}">
                                    <div class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $lDot }}"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-sm font-semibold {{ $lText }}">{{ $log->actionLabel() }}</span>
                                            @if ($log->red_alert)
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30 px-1.5 py-0.5 rounded">🚨 Red Alert</span>
                                            @endif
                                            <span class="text-xs text-gray-400 dark:text-gray-500 ml-auto whitespace-nowrap">{{ $log->created_at->format('M j, Y g:i A') }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">by {{ $log->changedBy?->name ?? 'Unknown' }}</div>
                                        @if ($log->message)
                                            <div class="mt-2 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line bg-white/70 dark:bg-gray-800/70 rounded px-3 py-2 border border-white dark:border-gray-600">{{ $log->message }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ================================================================
                     ACTION BUTTONS — always last
                     ================================================================ --}}

                {{-- Complete Only — post hold/approval --}}
                @if ($showCompleteOnly)
                    <div class="no-print w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-green-200 dark:border-green-700 px-6 py-5"
                         x-data="{ showConfirm: false }">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-sm font-semibold text-green-700 dark:text-green-400">Admin has cleared this permit.</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Mark the visit as complete once visitors have finished.</div>
                            </div>
                            <button type="button" @click="showConfirm = true"
                                class="shrink-0 inline-flex items-center px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors cursor-pointer">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Complete Visit
                            </button>
                        </div>
                        <div x-show="showConfirm" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
                            <div class="fixed inset-0 bg-black/50" @click="showConfirm = false"></div>
                            <div class="relative min-h-screen flex items-center justify-center p-4">
                                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6 text-center">
                                    <div class="mx-auto mb-4 text-green-500 w-14 h-14"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Complete Permit?</h3>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-5">Are you sure you want to mark this permit as completed?</p>
                                    <div class="flex gap-3 justify-center">
                                        <button @click="showConfirm = false" type="button" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 cursor-pointer">Cancel</button>
                                        <button type="submit" form="completePermitForm" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 cursor-pointer">Yes, Complete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- All Actions — normal in-progress --}}
                @if ($showAllActions)
                    <div class="no-print w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 px-6 py-5"
                         x-data="{ showCompleteConfirm: false, showCancelConfirm: false, showHoldModal: false }">
                        <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-3">Actions</div>
                        <div class="flex flex-col sm:flex-row gap-3">

                            {{-- Complete --}}
                            <button type="button" @click="showCompleteConfirm = true"
                                @disabled(! $isAdmin && ! $isAcceptedByCurrentUser && (int) ($permit->received_by ?? 0) !== 0)
                                class="flex-1 inline-flex justify-center items-center px-4 py-3 bg-green-600 dark:bg-green-700 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Complete
                            </button>

                            {{-- Did Not Arrive --}}
                            <form method="POST" action="{{ route('user.permits.cancel', $permit) }}" class="flex-1">
                                @csrf
                                <button type="button" @click="showCancelConfirm = true"
                                    @disabled(! $isAdmin && ! $isAcceptedByCurrentUser && (int) ($permit->received_by ?? 0) !== 0)
                                    class="w-full inline-flex justify-center items-center px-4 py-3 bg-red-600 dark:bg-red-700 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Did Not Arrive
                                </button>
                                <div x-show="showCancelConfirm" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
                                    <div class="fixed inset-0 bg-black/50" @click="showCancelConfirm = false"></div>
                                    <div class="relative min-h-screen flex items-center justify-center p-4">
                                        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6 text-center">
                                            <div class="mx-auto mb-4 text-red-500 w-14 h-14"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Did Not Arrive?</h3>
                                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-5">Are you sure you want to mark this permit as did not arrive?</p>
                                            <div class="flex gap-3 justify-center">
                                                <button @click="showCancelConfirm = false" type="button" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 cursor-pointer">Cancel</button>
                                                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 cursor-pointer">Yes, Did Not Arrive</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            {{-- On Hold --}}
                            <form method="POST" action="{{ route('user.permits.hold', $permit) }}" class="flex-1">
                                @csrf
                                <button type="button" @click="showHoldModal = true"
                                    class="w-full inline-flex justify-center items-center px-4 py-3 bg-orange-500 dark:bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    On Hold
                                </button>
                                <div x-show="showHoldModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
                                    <div class="fixed inset-0 bg-black/50" @click="showHoldModal = false"></div>
                                    <div class="relative min-h-screen flex items-center justify-center p-4">
                                        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Put Permit On Hold?</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Explain the issue so admin can review.</p>
                                            <div class="flex items-center justify-between p-3 mb-4 rounded-lg border {{ $hasRedAlert ? 'border-red-300 dark:border-red-600 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800' }}">
                                                <div>
                                                    <div class="text-sm font-semibold {{ $hasRedAlert ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-300' }}">🚨 Red Alert</div>
                                                    <div class="text-xs {{ $hasRedAlert ? 'text-red-500 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }} mt-0.5">
                                                        {{ $hasRedAlert ? 'Auto-detected: visitors may not meet the ' . $requiredDays . '-day interval.' : 'Toggle if visitors have not met the required days since last farm visit.' }}
                                                    </div>
                                                </div>
                                                <label class="relative inline-flex items-center cursor-pointer ml-3">
                                                    <input type="checkbox" name="red_alert" value="1" class="sr-only peer" @if($hasRedAlert) checked @endif>
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                                                </label>
                                            </div>
                                            <textarea name="hold_reason" rows="4" required placeholder="e.g. Visitor names do not match IDs presented..." class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 mb-4"></textarea>
                                            <div class="flex gap-3 justify-end">
                                                <button type="button" @click="showHoldModal = false" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 cursor-pointer">Cancel</button>
                                                <button type="submit" class="px-4 py-2 bg-orange-500 text-white text-sm rounded-lg hover:bg-orange-600 cursor-pointer">Confirm Hold</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- Complete confirm modal --}}
                        <div x-show="showCompleteConfirm" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
                            <div class="fixed inset-0 bg-black/50" @click="showCompleteConfirm = false"></div>
                            <div class="relative min-h-screen flex items-center justify-center p-4">
                                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6 text-center">
                                    <div class="mx-auto mb-4 text-green-500 w-14 h-14"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Complete Permit?</h3>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-5">Are you sure you want to mark this permit as completed?</p>
                                    <div class="flex gap-3 justify-center">
                                        <button @click="showCompleteConfirm = false" type="button" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 cursor-pointer">Cancel</button>
                                        <button type="submit" form="completePermitForm" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 cursor-pointer">Yes, Complete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </x-navbar>
</x-layout>