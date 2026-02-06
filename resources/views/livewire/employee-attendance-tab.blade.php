<div class="bg-white rounded-xl shadow p-5">

    <div class="flex justify-between items-center mb-4">
        <h3 class="font-semibold text-lg">Attendance ({{ \Carbon\Carbon::parse($month)->format('F Y') }})</h3>

        <input type="month"
               wire:model.live="month"
               class="border-gray-300 rounded-lg">
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left">Date</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Working Minutes</th>
                    <th class="px-4 py-2 text-left">Overtime</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($records as $r)
                <tr>
                    <td class="px-4 py-2">
                        {{ \Carbon\Carbon::parse($r->date)->format('d M Y') }}
                    </td>

                    <td class="px-4 py-2">
                        <span class="px-3 py-1 rounded-full text-xs
                            @if($r->status=='present') bg-green-100 text-green-700
                            @elseif($r->status=='half_day') bg-yellow-100 text-yellow-700
                            @elseif($r->status=='short_leave') bg-blue-100 text-blue-700
                            @else bg-red-100 text-red-700
                            @endif">
                            {{ ucfirst(str_replace('_',' ', $r->status)) }}
                        </span>
                    </td>

                    <td class="px-4 py-2">
                        {{ intdiv($r->working_minutes, 60) }}h 
                        {{ $r->working_minutes % 60 }}m
                    </td>

                    <td class="px-4 py-2 text-indigo-600">
                        {{ intdiv($r->overtime_minutes, 60) }}h 
                        {{ $r->overtime_minutes % 60 }}m
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-gray-500">
                        No attendance found for this month
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
</div>
