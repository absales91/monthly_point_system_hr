<x-app-layout>
<div class="max-w-7xl mx-auto px-6 py-8">

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">All Monthly Reports</h2>
        <p class="text-sm text-gray-500">
            View employee performance month-wise
        </p>
    </div>

    {{-- FILTERS --}}
    <form method="GET" class="bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-4">
        <select name="employee_id"
                class="border rounded-lg px-3 py-2 text-sm">
            <option value="">All Employees</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}"
                    @selected(request('employee_id') == $emp->id)>
                    {{ $emp->name }}
                </option>
            @endforeach
        </select>

        <input type="month" name="month"
               value="{{ request('month') }}"
               class="border rounded-lg px-3 py-2 text-sm">

        <button
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
            Filter
        </button>

        <a href="{{ route('monthly-reports-all.index') }}"
           class="text-sm text-gray-500 underline mt-2">
            Reset
        </a>
    </form>

    {{-- REPORT TABLE --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">Employee</th>
                    <th class="px-4 py-3">Month</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Rating</th>
                    <th class="px-4 py-3">Details</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($reports as $report)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold">
                        {{ $report->employee->name }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        {{ \Carbon\Carbon::parse($report->month)->format('F Y') }}
                    </td>

                    <td class="px-4 py-3 text-center font-bold">
                        {{ $report->total }}/100
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
                            <summary class="text-indigo-600 text-sm">
                                View
                            </summary>
                            <div class="mt-2 text-left text-xs text-gray-600 space-y-1">
                                <div>Attendance: {{ $report->attendance }}</div>
                                <div>Punctuality: {{ $report->punctuality }}</div>
                                <div>Behaviour: {{ $report->behaviour }}</div>
                                <div>Discipline: {{ $report->discipline ?? 0 }}</div>
                                <div>Participation: {{ $report->participation }}</div>
                                <div>Decision Making: {{ $report->decision_making }}</div>
                                <div>Creativity: {{ $report->creativity }}</div>
                                <div>Training: {{ $report->training }}</div>
                                <div>Test: {{ $report->test }}</div>
                            </div>
                        </details>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                        No reports found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
</x-app-layout>
