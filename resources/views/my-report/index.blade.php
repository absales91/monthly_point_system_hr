<x-app-layout>
<div class="max-w-6xl mx-auto px-6 py-8">

    <h2 class="text-2xl font-bold mb-6">My Monthly Reports</h2>

    @if($reports->isEmpty())
        <div class="bg-yellow-50 text-yellow-700 p-4 rounded-lg">
            No reports available yet.
        </div>
    @else

    <div class="bg-white shadow rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Month</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Rating</th>
                    <th class="px-4 py-3">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y">

                @foreach($reports as $report)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold">
                        {{ \Carbon\Carbon::parse($report->month)->format('F Y') }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        <span class="font-bold">{{ $report->total }}</span> / 100
                    </td>

                    <td class="px-4 py-3 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            @if($report->rating === 'Excellent') bg-green-100 text-green-700
                            @elseif($report->rating === 'Good') bg-blue-100 text-blue-700
                            @elseif($report->rating === 'Average') bg-yellow-100 text-yellow-700
                            @else bg-red-100 text-red-700
                            @endif">
                            {{ $report->rating }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-center">
                        <details class="cursor-pointer">
                            <summary class="text-indigo-600 text-sm">View</summary>
                            <div class="mt-2 text-left text-xs text-gray-600 space-y-1">
                                <div>Attendance: {{ $report->attendance }}</div>
                                <div>Punctuality: {{ $report->punctuality }}</div>
                                <div>Behaviour: {{ $report->behaviour }}</div>
                                <div>Participation: {{ $report->participation }}</div>
                                <div>Decision Making: {{ $report->decision_making }}</div>
                                <div>Creativity: {{ $report->creativity }}</div>
                                <div>Training: {{ $report->training }}</div>
                                <div>Test: {{ $report->test }}</div>
                            </div>
                        </details>
                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>

    @endif
</div>
</x-app-layout>
