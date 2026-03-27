<x-layout>
    <x-navbar :breadcrumbs="[
        ['label' => 'Permits', 'href' => route('admin.permits.index')],
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
                    // Red alert — computed from data
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

                    $hasRedAlert = $computedRedAlert || (bool) ($permit->red_alert ?? false);

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
                    $viewer        = Auth::user();
                    $isAdmin       = in_array((int) ($viewer->user_type ?? 0), [1, 2], true);
                    $currentStatus = (int) ($permit->status ?? 0);

                    $lastAdminLog  = $permit->logs->whereIn('action', [3, 4, 5, 9])->sortByDesc('created_at')->first();
                    $lastHoldLog   = $permit->logs->where('action', 2)->sortByDesc('created_at')->first();
                    $adminRejected = $lastAdminLog && (int) $lastAdminLog->action === 4;

                    // Zone 2 banner type
                    $bannerType = null;
                    if ($currentStatus === 4)                          $bannerType = 'on_hold';
                    elseif ($currentStatus === 3 && $adminRejected)    $bannerType = 'rejected';
                    elseif ($currentStatus === 5)                      $bannerType = 'returned';
                    elseif ($currentStatus === 2)                      $bannerType = 'completed';
                    elseif ($currentStatus === 3 && ! $adminRejected)  $bannerType = 'cancelled';

                    // Show red alert standalone banner only on active statuses with no other banner
                    $showRedAlertBanner = $hasRedAlert && $bannerType === null && in_array($currentStatus, [0, 1]);
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
                        .dark\\:text-white, .dark\\:text-white * { color: #111827 !important; }
                        .dark\\:text-gray-200, .dark\\:text-gray-200 * { color: #374151 !important; }
                        .dark\\:bg-gray-800 { background: white !important; }
                        .dark\\:bg-gray-900 { background: white !important; }
                        .dark\\:border-gray-700 { border-color: #e5e7eb !important; }
                    }
                </style>

                {{-- ================================================================
                     ZONE 1 — PERMIT DOCUMENT
                     ================================================================ --}}
                <div class="w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 px-8 pt-6 pb-6 mx-auto print-page print-bg">
                    <div class="print-wrap">

                        {{-- MOBILE --}}
                        <div class="no-print md:hidden">
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                                <div class="flex justify-between items-center">
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">FARM VISIT PERMIT {{ $permit->permit_id ?? '' }}</div>
                                    <button type="button" onclick="window.print()" class="inline-flex items-center p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white cursor-pointer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </button>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Date Filled: {{ $displayVal($dateFilled) }}</div>
                                <div class="mt-4 space-y-3 text-sm">
                                    <div><div class="font-semibold text-gray-700 dark:text-gray-200">Farm</div><div class="text-gray-900 dark:text-white">{{ $displayVal($farm) }}</div></div>
                                    <div><div class="font-semibold text-gray-700 dark:text-gray-200">Area/Department to Visit</div><div class="text-gray-900 dark:text-white">{{ $displayVal($areaName) }}</div></div>
                                    <div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Visitor Names</div>
                                        @if ($isDetailedMode)
                                            <div class="space-y-1 mt-1">
                                                @foreach ($namesData['groups'] as $i => $group)
                                                    <div class="{{ in_array($i, $alertGroupIdxs) ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-900 dark:text-white' }}">
                                                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">[{{ $group['origin'] }}]</span>
                                                        {{ $group['names'] }}
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
                                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">{{ $farmType === 1 ? 'Previous Poultry Farm Visited' : 'Previous Swine Farm Visited' }}</div>
                                        <div class="text-gray-900 dark:text-white">{{ $displayVal($previousFarm) }}</div>
                                    </div>
                                    <div><div class="font-semibold text-gray-700 dark:text-gray-200">Previous Farm Date of Visit</div><div class="text-gray-900 dark:text-white">{{ $displayVal($previousFarmDate) }}</div></div>
                                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Purpose</div>
                                        <div class="text-gray-900 dark:text-white">{{ $displayVal($permit->purpose ?? null) }}</div>
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
                                <button type="button" onclick="window.print()" class="no-print inline-flex items-center p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </button>
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
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100" colspan="2"><span class="font-bold">FARM:</span> {{ $displayVal($farm) }}</td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100" colspan="2"><span class="font-bold">Date Filled:</span> {{ $displayVal($dateFilled) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100" colspan="2">
                                            <div class="font-bold">VISITOR NAMES:</div>
                                            @if ($isDetailedMode)
                                                <div class="space-y-1 mt-1">
                                                    @foreach ($namesData['groups'] as $i => $group)
                                                        <div class="{{ in_array($i, $alertGroupIdxs) ? 'text-red-700' : '' }}">
                                                            <span class="font-semibold">{{ $group['origin'] }}:</span> {{ $group['names'] }}
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
                     PHOTOS
                     ================================================================ --}}
                @if ((($permit->photos ?? collect())->count() > 0) || in_array($currentStatus, [1, 4]))
                    <div class="no-print w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 px-6 pt-6 pb-4">
                        <livewire:permit-photo-upload :permit="$permit" :can-upload="false" />
                    </div>
                @endif

                {{-- Remarks --}}
                @if (is_string($permit->remarks ?? null) && trim((string) $permit->remarks) !== '')
                    <div class="no-print w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 px-6 pt-5 pb-5">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-2">Guard Remarks</div>
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-gray-900 dark:text-white whitespace-pre-line text-sm">{{ trim((string) $permit->remarks) }}</div>
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
                     ADMIN ACTION PANEL — always last
                     ================================================================ --}}
                @if ($isAdmin)

                    {{-- Respond to Hold --}}
                    @if ($currentStatus === 4)
                        <div class="no-print w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-orange-200 dark:border-orange-700 px-6 py-6">
                            <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-4">Respond to Hold</div>
                            <form method="POST" action="{{ route('admin.permits.respond', $permit) }}" x-data="{ action: '' }">
                                @csrf
                                <input type="hidden" name="action" x-bind:value="action">
                                <div class="mb-4">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Your Response <span class="normal-case text-gray-400">(optional)</span></label>
                                    <textarea name="admin_response" rows="3" placeholder="Add a note for the guard or permit creator..." class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <button type="submit" @click="action = 'approve'" class="inline-flex items-center px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 cursor-pointer">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Approve — Let Them In
                                    </button>
                                    <button type="submit" @click="action = 'return'" class="inline-flex items-center px-4 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 cursor-pointer">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                        Return for Correction
                                    </button>
                                    <button type="submit" @click="action = 'reject'" class="inline-flex items-center px-4 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 cursor-pointer">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Reject — Turn Away
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    {{-- Override Rejection --}}
                    @if ($currentStatus === 3 && $adminRejected)
                        <div class="no-print w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-yellow-200 dark:border-yellow-700 px-6 py-6">
                            <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-1">Override Rejection</div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">This permit was previously rejected. You can override and allow entry.</p>
                            <form method="POST" action="{{ route('admin.permits.override', $permit) }}">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Reason for Override <span class="normal-case text-gray-400">(optional)</span></label>
                                    <textarea name="admin_response" rows="2" placeholder="e.g. Purchasing confirmed exemption, visitors cleared to enter..." class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500"></textarea>
                                </div>
                                <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-yellow-500 text-white text-sm font-medium rounded-lg hover:bg-yellow-600 cursor-pointer">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Override — Let Them In
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Resubmit (Returned) --}}
                    @if ($currentStatus === 5)
                        <div class="no-print w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-purple-200 dark:border-purple-700 px-6 py-6">
                            <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-1">Returned for Correction</div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">This permit was returned. Edit and resubmit to put it back in the queue.</p>
                            <form method="POST" action="{{ route('admin.permits.resubmit', $permit) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 cursor-pointer">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Edit & Resubmit
                                </button>
                            </form>
                        </div>
                    @endif

                @endif

            </div>
        </div>
    </x-navbar>
</x-layout>