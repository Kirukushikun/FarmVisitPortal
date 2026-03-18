<x-layout>
    <x-navbar :breadcrumbs="[
        ['label' => 'Dashboard', 'href' => route('user.home')],
        ['label' => 'View Permit'],
    ]" :includeSidebar="true" :user="Auth::user()">
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <div class="p-4">
                @php
                    function permitPrintDuration(null|int|float|string $hours): string {
                        if ($hours === null) {
                            return '';
                        }

                        $hoursFloat = max(0, (float) $hours);
                        $totalSeconds = (int) round($hoursFloat * 3600);

                        $displayHours = intdiv($totalSeconds, 3600);
                        $displayMinutes = intdiv($totalSeconds % 3600, 60);
                        $displaySeconds = $totalSeconds % 60;

                        return sprintf('%02d:%02d:%02d', $displayHours, $displayMinutes, $displaySeconds);
                    }

                    function permitDisplayValue(mixed $value): string {
                        $value = is_string($value) ? trim($value) : $value;

                        if ($value === null) {
                            return 'N/A';
                        }

                        if (is_string($value) && $value === '') {
                            return 'N/A';
                        }

                        return (string) $value;
                    }

                    $dateFilled = $permit->created_at ? $permit->created_at->format('F j, Y') : '';
                    $farm = $permit->farmLocation?->name ?: '';
                    $farmType = (int) ($permit->farmLocation?->farm_type ?? 0);
                    $dateOfVisit = $permit->date_of_visit ? $permit->date_of_visit->format('F j, Y') : '';
                    $expectedDuration = permitPrintDuration($permit->expected_duration_hours);
                    $previousFarm = $permit->previous_farm_location ?? '';
                    $previousFarmDate = $permit->date_of_visit_previous_farm ? $permit->date_of_visit_previous_farm->format('F j, Y') : '';
                    $namesData = null;
                    if (is_string($permit->names) && trim($permit->names) !== '') {
                        $decoded = json_decode($permit->names, true);
                        if (is_array($decoded) && isset($decoded['mode'])) {
                            $namesData = $decoded;
                        }
                    }
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
                    
                    /* Dark mode styles for better contrast */
                    @media (prefers-color-scheme: dark) {
                        .dark .border-black { border-color: #d1d5db !important; }
                        .dark .text-gray-900 { color: #f9fafb !important; }
                        .dark .font-bold { color: #f3f4f6 !important; }
                    }
                </style>

                <div class="w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 px-8 pt-6 pb-6 mx-auto print-page print-bg">

                    <div class="print-wrap">
                        <div class="no-print md:hidden">
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                                <div class="flex justify-between items-center">
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">FARM VISIT PERMIT {{ $permit->permit_id ?? '' }}</div>
                                    <a href="{{ route('user.home') }}" class="inline-flex items-center justify-center p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white cursor-pointer">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                        </svg>
                                        <span class="text-sm">Back</span>
                                    </a>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Date Filled: {{ permitDisplayValue($dateFilled) }}</div>

                                <div class="mt-4 space-y-3 text-sm">
                                    <div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Area</div>
                                        <div class="text-gray-900 dark:text-white">{{ permitDisplayValue($permit->area->name ?? null) }}</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Farm</div>
                                        <div class="text-gray-900 dark:text-white">{{ permitDisplayValue($farm) }}</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Visitor Names</div>
                                        @if ($namesData && $namesData['mode'] === 'detailed')
                                            <div class="space-y-1 mt-1">
                                                @foreach ($namesData['groups'] as $group)
                                                    <div class="text-gray-900 dark:text-white">
                                                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">[{{ $group['origin'] }}]</span>
                                                        {{ $group['names'] }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif ($namesData)
                                            <div class="text-gray-900 dark:text-white whitespace-pre-line">{{ $namesData['value'] }}</div>
                                        @else
                                            <div class="text-gray-900 dark:text-white whitespace-pre-line">{{ permitDisplayValue($permit->names ?? null) }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Area/Department to Visit</div>
                                        <div class="text-gray-900 dark:text-white">{{ permitDisplayValue($permit->area->name ?? null) }}</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Destination</div>
                                        <div class="text-gray-900 dark:text-white">{{ permitDisplayValue($farm) }}</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Date of Visit</div>
                                        <div class="text-gray-900 dark:text-white">{{ permitDisplayValue($dateOfVisit) }}</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Expected Duration</div>
                                        <div class="text-gray-900 dark:text-white">{{ permitDisplayValue($expectedDuration) }}</div>
                                    </div>
                                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">{{ $farmType === 1 ? 'Previous Poultry Farm Visited' : 'Previous Swine Farm Visited' }}</div>
                                        <div class="text-gray-900 dark:text-white">{{ permitDisplayValue($previousFarm) }}</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Previous Farm Date of Visit</div>
                                        <div class="text-gray-900 dark:text-white">{{ permitDisplayValue($previousFarmDate) }}</div>
                                    </div>
                                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Purpose</div>
                                        <div class="text-gray-900 dark:text-white">{{ permitDisplayValue($permit->purpose ?? null) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="hidden md:block print:block">
                            <div class="flex justify-center mb-2">
                                <img src="{{ asset('images/BGC.png') }}" alt="BGC" class="h-20 w-48" />
                            </div>

                            <div class="flex justify-between items-center mb-2">
                                <div class="text-sm text-gray-900 dark:text-white">{{ permitDisplayValue($dateFilled) }}</div>
                                <a href="{{ route('user.home') }}" class="no-print inline-flex items-center justify-center p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white cursor-pointer">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
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
                                    <colgroup>
                                        <col style="width: 24%;">
                                        <col style="width: 46%;">
                                        <col style="width: 15%;">
                                        <col style="width: 15%;">
                                    </colgroup>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 w-1/4 align-top text-gray-900 dark:text-gray-100"><span class="font-bold text-gray-900 dark:text-gray-100">AREA:</span> {{ permitDisplayValue($permit->area ?? null) }}</td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 w-1/2 align-top text-gray-900 dark:text-gray-100"><span class="font-bold text-gray-900 dark:text-gray-100">FARM:</span> {{ permitDisplayValue($farm) }}</td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 w-1/4 align-top text-gray-900 dark:text-gray-100" colspan="2"><span class="font-bold text-gray-900 dark:text-gray-100">Date Filled:</span> {{ permitDisplayValue($dateFilled) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100" colspan="2">
                                            <div class="font-bold text-gray-900 dark:text-gray-100">VISITOR NAMES:</div>
                                            @if ($namesData && $namesData['mode'] === 'detailed')
                                                <div class="space-y-1 mt-1">
                                                    @foreach ($namesData['groups'] as $group)
                                                        <div><span class="font-semibold">{{ $group['origin'] }}:</span> {{ $group['names'] }}</div>
                                                    @endforeach
                                                </div>
                                            @elseif ($namesData)
                                                <div style="white-space: pre-line;">{{ $namesData['value'] }}</div>
                                            @else
                                                <div style="white-space: pre-line;">{{ permitDisplayValue($permit->names ?? null) }}</div>
                                            @endif
                                        </td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100" colspan="2">
                                            <div class="font-bold text-gray-900 dark:text-gray-100">Area / Section / Department to Visit:</div>
                                            <div>{{ permitDisplayValue($permit->area ?? null) }}</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top w-1/4 text-gray-900 dark:text-gray-100"><span class="font-bold text-gray-900 dark:text-gray-100">DESTINATION</span></td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100">{{ permitDisplayValue($farm) }}</td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top w-1/4 whitespace-nowrap text-gray-900 dark:text-gray-100"><span class="font-bold text-gray-900 dark:text-gray-100">DATE of VISIT</span></td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top w-1/4 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ permitDisplayValue($dateOfVisit) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 text-center text-gray-900 dark:text-gray-100" colspan="4">
                                            <span class="font-bold text-gray-900 dark:text-gray-100">Expected Duration:</span> {{ permitDisplayValue($expectedDuration) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 text-center text-gray-900 dark:text-gray-100" colspan="4">
                                            <div class="font-bold text-gray-900 dark:text-gray-100">Farm Travel History</div>
                                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                                @if ($farmType === 1)
                                                    (Must have not visited other Poultry Farm 3 days Prior to the Farm Visit)
                                                @else
                                                    (Must have not visited other Swine Farm 5 days Prior to the Farm Visit)
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top w-1/4 text-gray-900 dark:text-gray-100">
                                            <div class="font-bold text-center text-gray-900 dark:text-gray-100">{{ $farmType === 1 ? 'Previous Poultry Farm Visited' : 'Previous Swine Farm Visited' }}</div>
                                        </td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100">{{ permitDisplayValue($previousFarm) }}</td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top w-1/4 whitespace-nowrap text-gray-900 dark:text-gray-100">
                                            <div class="font-bold text-center text-gray-900 dark:text-gray-100">Date of Visit :</div>
                                        </td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top w-1/4 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ permitDisplayValue($previousFarmDate) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100">
                                            <div class="font-bold text-center text-gray-900 dark:text-gray-100">PURPOSE</div>
                                        </td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100" colspan="3">{{ permitDisplayValue($permit->purpose ?? null) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $viewer = Auth::user();
                    $isAdmin = (int) ($viewer->user_type ?? 0) === 1;
                    $isAcceptedByCurrentUser = (int) ($permit->received_by ?? 0) === (int) ($viewer->id ?? 0);
                @endphp

                @if (in_array((int) ($permit->status ?? 0), [1, 4]) || (($permit->photos ?? collect())->count() > 0))
                    <div class="no-print mt-6 w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 px-6 pt-6 pb-2">
                        <livewire:permit-photo-upload :permit="$permit" :can-upload="in_array((int) ($permit->status ?? 0), [1, 4])" />

                        @if ((int) ($permit->status ?? 0) === 1)
                            <form id="completePermitForm" method="POST" action="{{ route('user.permits.complete', $permit) }}" class="mt-6">
                                @csrf
                                <x-text-area
                                    label="Remarks"
                                    name="remarks"
                                    placeholder="Enter remarks (optional)"
                                    :value="old('remarks', $permit->remarks ?? '')"
                                />
                            </form>
                        @endif

                        @if (is_string($permit->remarks ?? null) && trim((string) $permit->remarks) !== '')
                            <div class="mt-6">
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Remarks</div>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-gray-900 dark:text-white whitespace-pre-line">{{ trim((string) $permit->remarks) }}</div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Action Buttons -->
                @if ((int) ($permit->status ?? 0) === 1)
                    <!-- Mobile -->
                    <div class="no-print mt-6 flex flex-col sm:flex-row gap-4 justify-center md:hidden" x-data="{ showCompleteConfirm: false, showCancelConfirm: false, showHoldModal: false }">
                        <!-- Complete Button -->
                        <button type="button" @click="showCompleteConfirm = true" @disabled(! $isAdmin && ! $isAcceptedByCurrentUser && (int) ($permit->received_by ?? 0) !== 0) class="flex-1 w-full inline-flex justify-center items-center px-4 py-3 bg-green-600 dark:bg-green-700 text-white font-medium rounded-lg hover:bg-green-700 dark:hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Complete
                        </button>

                        <!-- Complete Confirmation Modal -->
                        <div x-show="showCompleteConfirm" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                            <div class="fixed inset-0 bg-black/50" @click="showCompleteConfirm = false"></div>
                            <div class="relative min-h-screen flex items-center justify-center p-4">
                                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                                    <div class="text-center">
                                        <div class="mx-auto mb-4 text-green-500 w-16 h-16">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Complete Permit?</h3>
                                        <p class="text-gray-700 dark:text-gray-300 mb-4">Are you sure you want to mark this permit as completed?</p>
                                        <div class="flex gap-3 justify-center">
                                            <button @click="showCompleteConfirm = false" type="button" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">Cancel</button>
                                            <button type="submit" form="completePermitForm" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 cursor-pointer">Yes, Complete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Did Not Arrive -->
                        <form method="POST" action="{{ route('user.permits.cancel', $permit) }}" class="flex-1">
                            @csrf
                            <button type="button" @click="showCancelConfirm = true" @disabled(! $isAdmin && ! $isAcceptedByCurrentUser && (int) ($permit->received_by ?? 0) !== 0) class="w-full inline-flex justify-center items-center px-4 py-3 bg-red-600 dark:bg-red-700 text-white font-medium rounded-lg hover:bg-red-700 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-red-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Did Not Arrive
                            </button>
                            <div x-show="showCancelConfirm" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                <div class="fixed inset-0 bg-black/50" @click="showCancelConfirm = false"></div>
                                <div class="relative min-h-screen flex items-center justify-center p-4">
                                    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                                        <div class="text-center">
                                            <div class="mx-auto mb-4 text-red-500 w-16 h-16">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Did Not Arrive?</h3>
                                            <p class="text-gray-700 dark:text-gray-300 mb-4">Are you sure you want to mark this permit as did not arrive?</p>
                                            <div class="flex gap-3 justify-center">
                                                <button @click="showCancelConfirm = false" type="button" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">Cancel</button>
                                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 cursor-pointer">Yes, Did Not Arrive</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Hold Button -->
                        <form method="POST" action="{{ route('user.permits.hold', $permit) }}" class="flex-1">
                            @csrf
                            <button type="button" @click="showHoldModal = true" class="w-full inline-flex justify-center items-center px-4 py-3 bg-orange-500 dark:bg-orange-600 text-white font-medium rounded-lg hover:bg-orange-600 dark:hover:bg-orange-500 transition-colors cursor-pointer">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                On Hold
                            </button>
                            <div x-show="showHoldModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
                                <div class="fixed inset-0 bg-black/50" @click="showHoldModal = false"></div>
                                <div class="relative min-h-screen flex items-center justify-center p-4">
                                    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Put Permit On Hold?</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Explain the issue so the admin can review.</p>
                                        <textarea name="hold_reason" rows="4" required placeholder="e.g. Visitor names do not match IDs presented..." class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 mb-4"></textarea>
                                        <div class="flex gap-3 justify-end">
                                            <button type="button" @click="showHoldModal = false" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 cursor-pointer">Cancel</button>
                                            <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 cursor-pointer">Confirm Hold</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Desktop -->
                    <div class="no-print mt-6 hidden md:flex flex-col sm:flex-row gap-4 justify-center" x-data="{ showCompleteConfirm: false, showCancelConfirm: false, showHoldModal: false }">
                        <!-- Complete Button -->
                        <button type="button" @click="showCompleteConfirm = true" @disabled(! $isAdmin && ! $isAcceptedByCurrentUser && (int) ($permit->received_by ?? 0) !== 0) class="flex-1 max-w-xs w-full inline-flex justify-center items-center px-6 py-3 bg-green-600 dark:bg-green-700 text-white font-medium rounded-lg hover:bg-green-700 dark:hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Complete
                        </button>

                        <!-- Complete Confirmation Modal -->
                        <div x-show="showCompleteConfirm" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                            <div class="fixed inset-0 bg-black/50" @click="showCompleteConfirm = false"></div>
                            <div class="relative min-h-screen flex items-center justify-center p-4">
                                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                                    <div class="text-center">
                                        <div class="mx-auto mb-4 text-green-500 w-16 h-16">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Complete Permit?</h3>
                                        <p class="text-gray-700 dark:text-gray-300 mb-4">Are you sure you want to mark this permit as completed?</p>
                                        <div class="flex gap-3 justify-center">
                                            <button @click="showCompleteConfirm = false" type="button" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">Cancel</button>
                                            <button type="submit" form="completePermitForm" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 cursor-pointer">Yes, Complete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Did Not Arrive -->
                        <form method="POST" action="{{ route('user.permits.cancel', $permit) }}" class="flex-1 max-w-xs">
                            @csrf
                            <button type="button" @click="showCancelConfirm = true" @disabled(! $isAdmin && ! $isAcceptedByCurrentUser && (int) ($permit->received_by ?? 0) !== 0) class="w-full inline-flex justify-center items-center px-6 py-3 bg-red-600 dark:bg-red-700 text-white font-medium rounded-lg hover:bg-red-700 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-red-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Did Not Arrive
                            </button>
                            <div x-show="showCancelConfirm" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                <div class="fixed inset-0 bg-black/50" @click="showCancelConfirm = false"></div>
                                <div class="relative min-h-screen flex items-center justify-center p-4">
                                    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                                        <div class="text-center">
                                            <div class="mx-auto mb-4 text-red-500 w-16 h-16">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Did Not Arrive?</h3>
                                            <p class="text-gray-700 dark:text-gray-300 mb-4">Are you sure you want to mark this permit as did not arrive?</p>
                                            <div class="flex gap-3 justify-center">
                                                <button @click="showCancelConfirm = false" type="button" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">Cancel</button>
                                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 cursor-pointer">Yes, Did Not Arrive</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Hold Button -->
                        <form method="POST" action="{{ route('user.permits.hold', $permit) }}" class="flex-1 max-w-xs">
                            @csrf
                            <button type="button" @click="showHoldModal = true" class="w-full inline-flex justify-center items-center px-6 py-3 bg-orange-500 dark:bg-orange-600 text-white font-medium rounded-lg hover:bg-orange-600 dark:hover:bg-orange-500 transition-colors cursor-pointer">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                On Hold
                            </button>
                            <div x-show="showHoldModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
                                <div class="fixed inset-0 bg-black/50" @click="showHoldModal = false"></div>
                                <div class="relative min-h-screen flex items-center justify-center p-4">
                                    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Put Permit On Hold?</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Explain the issue so the admin can review.</p>
                                        <textarea name="hold_reason" rows="4" required placeholder="e.g. Visitor names do not match IDs presented..." class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 mb-4"></textarea>
                                        <div class="flex gap-3 justify-end">
                                            <button type="button" @click="showHoldModal = false" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 cursor-pointer">Cancel</button>
                                            <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 cursor-pointer">Confirm Hold</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- Hold / Admin Response Panel -->
                @if (in_array((int) ($permit->status ?? 0), [4, 5]))
                    <div class="no-print mt-6 w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-orange-200 dark:border-orange-700 px-6 py-6">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-semibold text-orange-600 dark:text-orange-400">
                                {{ (int) ($permit->status ?? 0) === 4 ? 'Permit On Hold' : 'Permit Returned for Correction' }}
                            </span>
                        </div>

                        @if ($permit->hold_reason)
                            <div class="mb-4">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Hold Reason</div>
                                <div class="rounded-lg bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 px-4 py-3 text-sm text-gray-900 dark:text-white whitespace-pre-line">{{ $permit->hold_reason }}</div>
                            </div>
                        @endif

                        @if ($permit->admin_response)
                            <div class="mb-4">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Admin Response</div>
                                <div class="rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-4 py-3 text-sm text-gray-900 dark:text-white whitespace-pre-line">{{ $permit->admin_response }}</div>
                            </div>
                        @endif

                        @if ((int) ($permit->status ?? 0) === 4 && $isAdmin)
                            <form method="POST" action="{{ route('user.permits.respond', $permit) }}" x-data="{ action: '' }">
                                @csrf
                                <input type="hidden" name="action" x-bind:value="action">
                                <div class="mb-3">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Your Response (optional)</label>
                                    <textarea name="admin_response" rows="3" placeholder="Add a note for the guard or permit creator..." class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <button type="submit" @click="action = 'approve'" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 cursor-pointer">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Approve — Let Them In
                                    </button>
                                    <button type="submit" @click="action = 'return'" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 cursor-pointer">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                        Return for Correction
                                    </button>
                                    <button type="submit" @click="action = 'reject'" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 cursor-pointer">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Reject — Turn Away
                                    </button>
                                </div>
                            </form>
                        @endif

                        @if ((int) ($permit->status ?? 0) === 5)
                            @php $canResubmit = $isAdmin || (int) ($permit->created_by ?? 0) === (int) ($viewer->id ?? 0); @endphp
                            @if ($canResubmit)
                                <form method="POST" action="{{ route('user.permits.resubmit', $permit) }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 cursor-pointer">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        Edit & Resubmit
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                @endif
                
            </div>
        </div>
    </x-navbar>
</x-layout>
