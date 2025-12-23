<x-app-layout>
<div class="max-w-5xl mx-auto px-6 py-8">

    <div class="flex justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold">Point Rules</h2>
            <p class="text-sm text-gray-500">Manage scoring logic</p>
        </div>

        <a href="{{ route('point-rules.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg">
            ➕ Add Rule
        </a>
    </div>

    <div class="bg-white shadow rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Category</th>
                    <th class="px-4 py-3">Label</th>
                    <th class="px-4 py-3">Max Points</th>
                    <th class="px-4 py-3">Manager Only</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($rules as $rule)
                <tr>
                    <td class="px-4 py-3 font-semibold">{{ $rule->category }}</td>
                    <td class="px-4 py-3 text-center">{{ $rule->label }}</td>
                    <td class="px-4 py-3 text-center">{{ $rule->max_points }}</td>
                    <td class="px-4 py-3 text-center">
                        {{ $rule->manager_only ? 'Yes' : 'No' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
</x-app-layout>
