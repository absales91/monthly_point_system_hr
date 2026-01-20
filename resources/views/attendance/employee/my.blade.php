<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-6">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">
                My Attendance
            </h2>

            <!-- <div class="flex gap-3">
                <form method="POST" action="{{ route('attendance.checkin') }}">
                    @csrf
                    <button
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                        Check In
                    </button>
                </form>

                <form method="POST" action="{{ route('attendance.checkout') }}">
                    @csrf
                    <button
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
                        Check Out
                    </button>
                </form>
            </div> -->
        </div>

        {{-- Attendance Table Card --}}
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Date</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Check In</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Check Out</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-600">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($records as $r)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ \Carbon\Carbon::parse($r->date)->format('d M Y') }}
                                </td>

                                <td class="px-6 py-4">
                                    @php
                                        $statusClasses = match($r->status) {
                                            'present' => 'bg-green-100 text-green-700',
                                            'half_day' => 'bg-yellow-100 text-yellow-700',
                                            'leave' => 'bg-blue-100 text-blue-700',
                                            default => 'bg-red-100 text-red-700',
                                        };
                                    @endphp

                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses }}">
                                        {{ ucfirst(str_replace('_', ' ', $r->status)) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $r->check_in ? \Carbon\Carbon::parse($r->check_in)->format('h:i A') : '-' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $r->check_out ? \Carbon\Carbon::parse($r->check_out)->format('h:i A') : '-' }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('employee.attendance.show', $r->date) }}"
                                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium 
                                              text-blue-700 bg-blue-100 hover:bg-blue-200 
                                              rounded-lg transition">
                                        View Punches
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                    No attendance records found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
