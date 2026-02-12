<x-app-layout>
    <div class="max-w-7xl mx-auto px-6 py-6">

        {{-- BACK --}}
        <a href="{{ route('staff.show',$staff->id) }}"
            class="text-blue-600 font-medium">← Back</a>

        {{-- HEADER --}}
        <div class="flex justify-between items-center mt-4">
            <h2 class="text-2xl font-bold">{{ $staff->name }}</h2>
            <button class="border px-4 py-2 rounded-lg text-blue-600">
                Download Report
            </button>
        </div>

        {{-- MONTH PICKER --}}
        <div class="mt-4">
            <form method="GET">
                <input type="month" name="month" value="{{ $month }}"
                    class="border rounded-lg px-3 py-2"
                    onchange="this.form.submit()">
            </form>
        </div>

        {{-- TOP SUMMARY BAR --}}
        <div class="grid grid-cols-2 md:grid-cols-7 gap-3 bg-white p-5 rounded-xl shadow mt-4">

            <div>
                <p class="text-xs text-gray-500">Days</p>
                <p class="text-lg font-bold">{{ $records->count() }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Present</p>
                <p class="text-lg font-bold text-green-600">{{ $present }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Absent</p>
                <p class="text-lg font-bold">{{ $absent }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Half Day</p>
                <p class="text-lg font-bold">{{ $halfDay }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Leave</p>
                <p class="text-lg font-bold">{{ $leave }}</p>
            </div>

        </div>

        {{-- DAY-WISE LIST --}}
        <div class="space-y-3 mt-6">

            @forelse($records as $r)

            <div class="bg-white rounded-xl shadow p-5 flex justify-between items-center">

                <div>
                    <p class="font-semibold">
                        {{ \Carbon\Carbon::parse($r->date)->format('d M l') }}
                    </p>

                    <p class="text-sm text-red-500">
                        {{ ucfirst($r->status) }}
                    </p>

                    <a href="#" class="text-sm text-blue-600">Add Note • Logs</a>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <button
                        wire:click="$dispatch('open-attendance-modal', {
    employeeId: {{ $staff->id }},
    date: '{{ $r->date }}',
    status: 'present'
})"
                        class="border px-4 py-2 rounded-lg hover:bg-green-50">
                        P | Present
                    </button>

                    <button
                        wire:click="$dispatch('open-attendance-modal', {
    employeeId: {{ $staff->id }},
    date: '{{ $r->date }}',
    status: 'half_day'
})"
                        class="border px-4 py-2 rounded-lg hover:bg-yellow-50">
                        HD | Half Day
                    </button>

                    <button
                        wire:click="$dispatch('open-attendance-modal', {
    employeeId: {{ $staff->id }},
    date: '{{ $r->date }}',
    status: 'leave'
})"
                        class="border px-4 py-2 rounded-lg hover:bg-indigo-50">
                        L | Leave
                    </button>




                </div>

            </div>

            @empty
            <p class="text-gray-500">No records found for this month.</p>
            @endforelse

        </div>

    </div>
</x-app-layout>