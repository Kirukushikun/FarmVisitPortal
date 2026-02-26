<x-layout>
    <x-navbar :breadcrumbs="[
        ['label' => 'Permits', 'href' => route('admin.permits.index')],
        ['label' => 'Permit ' . ($permit->permit_id ?? '')],
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
                        .print-page { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; box-shadow: none !important; border-radius: 0 !important; }
                        .print-wrap { padding: 0 !important; }
                        .print-bg { background: white !important; }
                    }
                </style>

                <div class="w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg px-8 pt-6 pb-6 mx-auto print-page print-bg">
                    <div class="flex justify-end mb-4 no-print">
                        <button type="button" onclick="window.print()" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-gray-800 rounded-lg hover:bg-gray-900">
                            Print
                        </button>
                    </div>

                    <div class="print-wrap">
                        <div class="no-print md:hidden">
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                                <div class="text-lg font-semibold text-gray-900 dark:text-white">FARM VISIT PERMIT {{ $permit->permit_id ?? '' }}</div>
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
                            <div class="text-sm text-gray-900 dark:text-white mb-2">{{ permitDisplayValue($dateFilled) }}</div>

                            <div class="flex justify-center mb-2">
                                <img src="{{ asset('images/BGC.png') }}" alt="BGC" class="h-12 w-auto" />
                            </div>

                            <div class="border-t border-b border-black py-2 text-center">
                                <div class="text-xl font-semibold tracking-wide italic text-gray-900 dark:text-white" style="font-family: 'Times New Roman', Times, serif;">
                                    FARM VISIT PERMIT {{ $permit->permit_id ?? '' }}
                                </div>
                            </div>

                            <div class="mt-4">
                                <table class="w-full border border-black" style="border-collapse: collapse; font-family: 'Times New Roman', Times, serif;">
                                    <colgroup>
                                        <col style="width: 24%;">
                                        <col style="width: 46%;">
                                        <col style="width: 15%;">
                                        <col style="width: 15%;">
                                    </colgroup>
                                    <tr>
                                        <td class="border border-black p-2 w-1/4 align-top"><span class="font-bold">AREA:</span> {{ permitDisplayValue($permit->area ?? null) }}</td>
                                        <td class="border border-black p-2 w-1/2 align-top"><span class="font-bold">FARM:</span> {{ permitDisplayValue($farm) }}</td>
                                        <td class="border border-black p-2 w-1/4 align-top" colspan="2"><span class="font-bold">Date Filled:</span> {{ permitDisplayValue($dateFilled) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-black p-2 align-top" colspan="2"><span class="font-bold">NAME:</span> {{ permitDisplayValue($permit->names ?? null) }}</td>
                                        <td class="border border-black p-2 align-top" colspan="2"><span class="font-bold">Area / Section / Department to Visit:</span> {{ permitDisplayValue($permit->area_to_visit ?? null) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-black p-2 align-top w-1/4"><span class="font-bold">DESTINATION</span></td>
                                        <td class="border border-black p-2 align-top">{{ permitDisplayValue($destination) }}</td>
                                        <td class="border border-black p-2 align-top w-1/4 whitespace-nowrap"><span class="font-bold">DATE of VISIT</span></td>
                                        <td class="border border-black p-2 align-top w-1/4 whitespace-nowrap">{{ permitDisplayValue($dateOfVisit) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-black p-2 text-center" colspan="4">
                                            <span class="font-bold">Expected Duration:</span> {{ permitDisplayValue($expectedDuration) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-black p-2 text-center" colspan="4">
                                            <div class="font-bold">Farm Travel History</div>
                                            <div class="text-sm">(Must have not visited other Poultry Farm 3 days Prior to the Farm Visit)</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-black p-2 align-top w-1/4">
                                            <div class="font-bold text-center">Previous Farm Visited</div>
                                        </td>
                                        <td class="border border-black p-2 align-top">{{ permitDisplayValue($previousFarm) }}</td>
                                        <td class="border border-black p-2 align-top w-1/4 whitespace-nowrap">
                                            <div class="font-bold text-center">Date of Visit :</div>
                                        </td>
                                        <td class="border border-black p-2 align-top w-1/4 whitespace-nowrap">{{ permitDisplayValue($previousFarmDate) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-black p-2 align-top">
                                            <div class="font-bold text-center">PURPOSE</div>
                                        </td>
                                        <td class="border border-black p-2 align-top" colspan="3">{{ permitDisplayValue($permit->purpose ?? null) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-navbar>
</x-layout>
