<x-app-layout>
    <div class="max-w-7xl mx-auto px-6 py-8">

        {{-- Header --}}
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                🗓 Attendance Management
            </h2>
            <p class="text-sm text-gray-500">
                Date: {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
            </p>
        </div>

        {{-- Success Message --}}
        @if (session()->has('success'))
            <div class="mb-4 rounded-lg bg-green-100 border border-green-300 text-green-800 px-4 py-3 flex justify-between items-center">
                <span>{{ session('success') }}</span>
                <button wire:click="$refresh" class="font-bold">✕</button>
            </div>
        @endif

        {{-- Attendance Form --}}
        <div class="bg-white rounded-xl shadow mb-6">
            <div class="px-6 py-4 border-b">
                <h3 class="font-semibold text-gray-700">Mark Attendance</h3>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">

                    {{-- Employee --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Employee
                        </label>
                        <select wire:model="employee_id"
                                class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Date
                        </label>
                        <input type="date"
                               wire:model="date"
                               class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('date')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Status
                        </label>
                        <select wire:model="status"
                                class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="present">✅ Present</option>
                            <option value="half_day">🌓 Half Day</option>
                            <option value="absent">❌ Absent</option>
                            <option value="leave">🌴 Leave</option>
                        </select>
                    </div>

                    {{-- Save Button --}}
                    <div>
                        <button wire:click="save"
                                wire:loading.attr="disabled"
                                class="w-full inline-flex justify-center items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-white font-semibold hover:bg-indigo-700 transition">
                            💾 Save
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- Attendance Table --}}
        <div class="bg-white rounded-xl shadow">
            <div class="px-6 py-4 border-b">
                <h3 class="font-semibold text-gray-700">Attendance List</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                Employee
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                Check In
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                                Check Out
                            </th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($records as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-800">
                                {{ $r->employee->name }}
                            </td>

                            <td class="px-6 py-3">
                                @php
                                    $colors = [
                                        'present' => 'green',
                                        'half_day' => 'yellow',
                                        'absent' => 'red',
                                        'leave' => 'blue'
                                    ];
                                    $color = $colors[$r->status] ?? 'gray';
                                @endphp
                                <span class="inline-flex items-center rounded-full bg-{{ $color }}-100 px-3 py-1 text-xs font-semibold text-{{ $color }}-800">
                                    {{ ucfirst(str_replace('_',' ',$r->status)) }}
                                </span>
                            </td>

                            <td class="px-6 py-3 text-gray-600">
                                {{ $r->check_in ?? '-' }}
                            </td>

                            <td class="px-6 py-3 text-gray-600">
                                {{ $r->check_out ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-6 text-center text-gray-500">
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
