<div>
@if($show)

<div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">

    <div class="bg-white rounded-xl shadow-xl w-[420px] p-6">

        {{-- HEADER --}}
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">
                Mark Attendance
            </h3>

            <button wire:click="$set('show', false)"
                    class="text-gray-400 hover:text-gray-700 text-xl font-bold">
                ×
            </button>
        </div>

        {{-- EMPLOYEE + DATE --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4 text-sm">
            <p class="font-medium text-gray-800">
                {{ $staff?->name }}
            </p>
            <p class="text-gray-600">
                {{ \Carbon\Carbon::parse($date)->format('d M Y (l)') }}
            </p>
            <p class="mt-1 text-sm">
                Status:
                <span class="font-semibold text-blue-700 uppercase">
                    
                    {{ str_replace('_',' ',$status) }}
                </span>
            </p>
        </div>

        {{-- TIME INPUTS --}}
        <div class="grid grid-cols-2 gap-4 mb-5">

            <div>
                <label class="text-xs text-gray-600 mb-1 block">
                    Start Time
                </label>
                <input type="time"
                       wire:model="start_time"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="text-xs text-gray-600 mb-1 block">
                    End Time
                </label>
                <input type="time"
                       wire:model="end_time"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex justify-end gap-3">

            <button wire:click="$set('show', false)"
                    class="border px-4 py-2 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
            </button>

            <button wire:click="saveAttendance"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                Save Attendance
            </button>

        </div>

    </div>

</div>

@endif
</div>
