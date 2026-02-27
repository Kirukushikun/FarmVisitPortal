<x-layout>
    <x-navbar :breadcrumbs="[
        ['label' => 'Dashboard', 'href' => route('user.home')],
        ['label' => 'View Permit'],
    ]" :includeSidebar="true" :user="Auth::user()">
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <div class="p-4">
                @php
                    function permitPrintDuration(?int $seconds): string {
                        if ($seconds === null) {
                            return '';
                        }

                        $seconds = max(0, (int) $seconds);
                        $hours = intdiv($seconds, 3600);
                        $minutes = intdiv($seconds % 3600, 60);
                        $remainingSeconds = $seconds % 60;

                        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
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
                    $destination = $permit->destinationLocation?->name ?: '';
                    $dateOfVisit = $permit->date_of_visit ? $permit->date_of_visit->format('F j, Y') : '';
                    $expectedDuration = permitPrintDuration($permit->expected_duration_seconds);
                    $previousFarm = $permit->previousFarmLocation?->name ?: '';
                    $previousFarmDate = $permit->date_of_visit_previous_farm ? $permit->date_of_visit_previous_farm->format('F j, Y') : '';
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
                                        <div class="text-gray-900 dark:text-white">{{ permitDisplayValue($permit->area ?? null) }}</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Farm</div>
                                        <div class="text-gray-900 dark:text-white">{{ permitDisplayValue($farm) }}</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Name</div>
                                        <div class="text-gray-900 dark:text-white">{{ permitDisplayValue($permit->names ?? null) }}</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Area / Section / Department to Visit</div>
                                        <div class="text-gray-900 dark:text-white">{{ permitDisplayValue($permit->area_to_visit ?? null) }}</div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Destination</div>
                                        <div class="text-gray-900 dark:text-white">{{ permitDisplayValue($destination) }}</div>
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
                                        <div class="font-semibold text-gray-700 dark:text-gray-200">Previous Farm Visited</div>
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
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100" colspan="2"><span class="font-bold text-gray-900 dark:text-gray-100">NAME:</span> {{ permitDisplayValue($permit->names ?? null) }}</td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100" colspan="2"><span class="font-bold text-gray-900 dark:text-gray-100">Area / Section / Department to Visit:</span> {{ permitDisplayValue($permit->area_to_visit ?? null) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top w-1/4 text-gray-900 dark:text-gray-100"><span class="font-bold text-gray-900 dark:text-gray-100">DESTINATION</span></td>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top text-gray-900 dark:text-gray-100">{{ permitDisplayValue($destination) }}</td>
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
                                            <div class="text-sm text-gray-700 dark:text-gray-300">(Must have not visited other Poultry Farm 3 days Prior to the Farm Visit)</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-gray-900 dark:border-gray-300 p-2 align-top w-1/4 text-gray-900 dark:text-gray-100">
                                            <div class="font-bold text-center text-gray-900 dark:text-gray-100">Previous Farm Visited</div>
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

                <!-- Action Buttons -->
                @if ($permit->status < 3)
                    <div class="no-print mt-6 flex flex-col sm:flex-row gap-4 justify-center md:hidden">
                        <form method="POST" action="{{ route('user.permits.complete', $permit) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-green-600 dark:bg-green-700 text-white font-medium rounded-lg hover:bg-green-700 dark:hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Complete
                            </button>
                        </form>

                        <form method="POST" action="{{ route('user.permits.cancel', $permit) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-red-600 dark:bg-red-700 text-white font-medium rounded-lg hover:bg-red-700 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-red-400 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Did Not Arrive
                            </button>
                        </form>
                    </div>

                    <div class="no-print mt-6 hidden md:flex flex-col sm:flex-row gap-4 justify-center">
                        <form method="POST" action="{{ route('user.permits.complete', $permit) }}" class="flex-1 max-w-xs">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 bg-green-600 dark:bg-green-700 text-white font-medium rounded-lg hover:bg-green-700 dark:hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Complete
                            </button>
                        </form>

                        <form method="POST" action="{{ route('user.permits.cancel', $permit) }}" class="flex-1 max-w-xs">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 bg-red-600 dark:bg-red-700 text-white font-medium rounded-lg hover:bg-red-700 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-red-400 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Did Not Arrive
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </x-navbar>
</x-layout>
