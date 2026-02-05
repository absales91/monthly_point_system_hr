<x-app-layout>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <div class="max-w-7xl mx-auto px-6 py-8">

        {{-- Header --}}
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    🗓 Attendance Management
                </h2>
            </div>

           
        </div>


        {{-- Success Message --}}
        @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-100 border border-green-300 text-green-800 px-4 py-3 flex justify-between items-center">
            <span>{{ session('success') }}</span>
            <button wire:click="$refresh" class="font-bold">✕</button>
        </div>
        @endif
        {{-- TOP SUMMARY BAR --}}
        <div class="grid grid-cols-2 md:grid-cols-8 gap-3 mb-6 bg-white p-5 rounded-xl shadow">

            <div>
                <p class="text-xs text-gray-500">Total Staff</p>
                <p class="text-lg font-bold">{{ $totalStaff }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Present</p>
                <p class="text-lg font-bold text-green-600">
                    {{ $present }}
                </p>
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
                <p class="text-xs text-gray-500">Overtime Hours</p>
                <p class="text-lg font-bold">
                    {{ $overtimeHours }}h {{ $overtimeMins }}m
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Fine Hours</p>
                <p class="text-lg font-bold">
                    {{ $fineHours }}h {{ $fineMins }}m
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Leave</p>
                <p class="text-lg font-bold">{{ $leave }}</p>
            </div>

        </div>


        <div class="flex gap-4 mb-5">
            <button class="px-4 py-2 bg-gray-100 rounded-lg">📅 Leaves</button>
            <button class="px-4 py-2 bg-blue-100 text-blue-600 rounded-lg font-semibold">🗓 Daily Work Entry</button>
            <button class="px-4 py-2 bg-gray-100 rounded-lg">💰 Fine</button>
            <button class="px-4 py-2 bg-gray-100 rounded-lg">⏱ Overtime</button>
        </div>



        {{-- Attendance Table --}}
        <livewire:attendance-admin />



    </div>
</x-app-layout>