<x-app-layout>
<div class="max-w-6xl mx-auto px-6 py-8">

    <div class="flex justify-between mb-6">
        <h2 class="text-2xl font-bold">🏆 Employee of the Month</h2>

        @if(isAdmin())
        <form method="POST" action="{{ route('employee-of-month.announce') }}"
              class="flex gap-2">
            @csrf
            <input type="month" name="month"
                   class="border rounded-lg px-3 py-2" required>
            <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg">
                Announce
            </button>
        </form>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($records as $row)
        <div class="bg-white rounded-xl shadow p-6">
            <h4 class="font-semibold text-lg">
                {{ \Carbon\Carbon::parse($row->month)->format('F Y') }}
            </h4>
            <p class="text-sm text-gray-500 mt-1">
                🎉 {{ $row->employee->name }}
            </p>
            <p class="mt-2 text-sm">
                Score: <strong>{{ $row->points }}</strong>
            </p>
        </div>
        @endforeach
    </div>

</div>
</x-app-layout>
