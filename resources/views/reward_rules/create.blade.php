<x-app-layout>
<div class="max-w-3xl mx-auto px-6 py-8">

    {{-- Header --}}
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800">
            🎁 Create Reward Rule
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            Define how employee points convert into rewards
        </p>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl shadow p-8">

        <form method="POST" action="{{ route('reward-rules.store') }}" class="space-y-6">
            @csrf

            {{-- Reward Type --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Reward Type
                </label>
                <select name="reward_type"
                        class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                    <option value="paid_leave">Paid Leave</option>
                    <option value="cash">Cash Bonus</option>
                    <option value="badge">Badge / Recognition</option>
                </select>
            </div>

            {{-- Reward Name --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Reward Name
                </label>
                <input type="text" name="reward_name"
                       placeholder="e.g. 1 Paid Leave / ₹1000 Bonus"
                       class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500"
                       required>
            </div>

            {{-- Points Required --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Points Required
                </label>
                <input type="number" name="point_threshold"
                       placeholder="e.g. 1000"
                       min="1"
                       class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500"
                       required>
            </div>

            {{-- Reward Value --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Reward Value
                </label>
                <input type="number" name="reward_value"
                       placeholder="e.g. 1 (Leave) / 1000 (Cash)"
                       min="1"
                       class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500"
                       required>
            </div>

            {{-- Monthly Limit --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Maximum Rewards Per Month
                </label>
                <input type="number" name="max_per_month"
                       value="1"
                       min="1"
                       class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500">
            </div>

            {{-- Carry Forward --}}
            <div class="flex items-center gap-3">
                <input type="checkbox" name="carry_forward"
                       class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                <span class="text-sm text-gray-600">
                    Allow carry forward of unused points
                </span>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-6 border-t">
                <a href="{{ route('reward-rules.index') }}"
                   class="text-gray-600 hover:text-gray-800 text-sm font-semibold">
                    ← Back to Reward Rules
                </a>

                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow">
                    Save Reward Rule
                </button>
            </div>

        </form>

    </div>

</div>
</x-app-layout>
