<x-app-layout>
<div class="max-w-7xl mx-auto px-6 py-8">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">
                🎁 Reward Rules
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Define how points convert into paid leave, bonuses & rewards
            </p>
        </div>

        <a href="{{ route('reward-rules.create') }}"
           class="mt-4 md:mt-0 bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow">
            + Add Reward Rule
        </a>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Reward Rules Table --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">

        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600">
                <tr>
                    <th class="px-5 py-3 text-left">Reward</th>
                    <th class="px-5 py-3 text-center">Type</th>
                    <th class="px-5 py-3 text-center">Points Required</th>
                    <th class="px-5 py-3 text-center">Reward Value</th>
                    <th class="px-5 py-3 text-center">Monthly Cap</th>
                    <th class="px-5 py-3 text-center">Carry Forward</th>
                    <th class="px-5 py-3 text-center">Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rules as $rule)
                    <tr class="border-t hover:bg-gray-50 transition">

                        {{-- Reward Name --}}
                        <td class="px-5 py-4 font-semibold text-gray-800">
                            {{ $rule->reward_name }}
                        </td>

                        {{-- Reward Type --}}
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                @if($rule->reward_type === 'paid_leave') bg-green-100 text-green-700
                                @elseif($rule->reward_type === 'cash') bg-blue-100 text-blue-700
                                @else bg-gray-200 text-gray-700
                                @endif">
                                {{ strtoupper(str_replace('_',' ', $rule->reward_type)) }}
                            </span>
                        </td>

                        {{-- Points --}}
                        <td class="px-5 py-4 text-center font-semibold">
                            {{ $rule->point_threshold }}
                        </td>

                        {{-- Reward Value --}}
                        <td class="px-5 py-4 text-center">
                            {{ $rule->reward_value }}
                        </td>

                        {{-- Monthly Cap --}}
                        <td class="px-5 py-4 text-center">
                            {{ $rule->max_per_month }}
                        </td>

                        {{-- Carry Forward --}}
                        <td class="px-5 py-4 text-center">
                            @if($rule->carry_forward)
                                <span class="text-green-600 font-semibold">Yes</span>
                            @else
                                <span class="text-red-500 font-semibold">No</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4 text-center">
                            @if($rule->is_active)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    Disabled
                                </span>
                            @endif
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-gray-500">
                            No reward rules created yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</div>
</x-app-layout>
