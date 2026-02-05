<div class="space-y-4"> 
 <div 
    x-data
    x-init="
        flatpickr($refs.datepicker, {
            dateFormat: 'Y-m-d',
             defaultDate: 'today',
           
            maxDate: 'today',        // ✅ DISABLE FUTURE DATES
            onChange: (selectedDates, dateStr) => {
                $wire.set('date', dateStr)
            }
        })
    "
>
    <input 
        x-ref="datepicker"
        type="text"
        class="border-gray-300 rounded-lg shadow-sm px-4 py-2"
        placeholder="Select date"
    >
</div>



@foreach($employees as $emp)

@php
    $record = $records->firstWhere('employee_id', $emp->id);
@endphp

<div class="bg-white rounded-xl shadow p-5 flex justify-between items-center">

    <div>
        <a href="{{route('employees.attendance', ['id' => $emp->id])}}"><p class="font-semibold text-gray-800">{{ $emp->name }}</p></a>
        <p class="text-sm {{ $record ? 'text-green-600' : 'text-red-500' }}">
            {{ $record->status ?? 'Not Marked' }}
        </p>
    </div>

    <div class="grid grid-cols-3 gap-3">

        <button wire:click="markAttendance({{ $emp->id }}, 'present')"
                class="border px-4 py-2 rounded-lg
                {{ $record?->status == 'present' ? 'bg-green-100 border-green-400' : '' }}">
            P | Present
        </button>

        <button wire:click="markAttendance({{ $emp->id }}, 'half_day')"
                class="border px-4 py-2 rounded-lg
                {{ $record?->status == 'half_day' ? 'bg-yellow-100 border-yellow-400' : '' }}">
            HD | Half Day
        </button>

        <button wire:click="markAttendance({{ $emp->id }}, 'absent')"
                class="border px-4 py-2 rounded-lg
                {{ $record?->status == 'absent' ? 'bg-red-100 border-red-400' : '' }}">
            A | Absent
        </button>
        <button wire:click="markAttendance({{ $emp->id }}, 'short_leave')"
                class="border px-4 py-2 rounded-lg
                {{ $record?->status == 'short_leave' ? 'bg-red-100 border-red-400' : '' }}">
            SL | Short Leave
        </button>
        <button wire:click="markAttendance({{ $emp->id }}, 'leave')"
                class="border px-4 py-2 rounded-lg
                {{ $record?->status == 'leave' ? 'bg-blue-100 border-blue-400' : '' }}">
            L | Leave
        </button>

    </div>
</div>

@endforeach

</div>