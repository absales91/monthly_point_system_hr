<x-app-layout>
<div class="max-w-3xl mx-auto px-6 py-8">

    <h2 class="text-2xl font-bold mb-6">Add Monthly Report</h2>

    {{-- Errors --}}
    @if ($errors->any())
        <div class="mb-4 bg-red-50 text-red-700 p-4 rounded-lg">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('monthly-reports.store') }}"
          class="bg-white shadow rounded-xl p-6 space-y-5">
        @csrf

        {{-- Employee --}}
        <div>
            <label class="block text-sm font-semibold">Employee</label>
            <select name="employee_id" class="w-full border rounded-lg px-4 py-2" required>
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Month --}}
        <div>
            <label class="block text-sm font-semibold">Month</label>
            <input type="month" name="month"
                   class="w-full border rounded-lg px-4 py-2" required>
        </div>

        {{-- Dynamic Point Rules --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            @foreach($rules as $rule)
                @php
                    $disabled = $rule->manager_only && !auth()->user()->canManage();
                @endphp

                <div>
                    <label class="block text-sm font-semibold">
                        {{ $rule->label }}
                        <span class="text-xs text-gray-400">
                            (Max {{ $rule->max_points }})
                        </span>
                    </label>

                    <input type="number"
                           name="{{ $rule->category }}"
                           max="{{ $rule->max_points }}"
                           min="0"
                           {{ $disabled ? 'disabled' : '' }}
                           class="w-full border rounded-lg px-4 py-2
                                  {{ $disabled ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                </div>
            @endforeach

        </div>

        {{-- Actions --}}
        <div class="flex justify-between pt-6">
            <a href="{{ route('dashboard') }}"
               class="text-sm text-gray-500 hover:underline">
                ← Back
            </a>

            <button
                class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700">
                Save Monthly Report
            </button>
        </div>

    </form>

</div>
</x-app-layout>
