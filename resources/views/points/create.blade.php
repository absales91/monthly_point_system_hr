<x-app-layout>
<div class="max-w-xl mx-auto px-6 py-8">

    <h2 class="text-2xl font-bold mb-6">Create Point Rule</h2>

    @if ($errors->any())
        <div class="mb-4 bg-red-50 text-red-700 p-4 rounded-lg">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('point-rules.store') }}"
          class="bg-white shadow rounded-xl p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-semibold">Category (key)</label>
            <input name="category" value="{{ old('category') }}"
                   class="w-full border rounded-lg px-4 py-2" required>
        </div>

        <div>
            <label class="block text-sm font-semibold">Label</label>
            <input name="label" value="{{ old('label') }}"
                   class="w-full border rounded-lg px-4 py-2" required>
        </div>

        <div>
            <label class="block text-sm font-semibold">Max Points</label>
            <input type="number" name="max_points"
                   value="{{ old('max_points') }}"
                   class="w-full border rounded-lg px-4 py-2" required>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="manager_only" value="1">
            <label class="text-sm">Only Manager/Admin can assign</label>
        </div>

        <div class="flex justify-between pt-4">
            <a href="{{ route('point-rules.index') }}"
               class="text-sm text-gray-500 hover:underline">← Back</a>

            <button class="bg-indigo-600 text-white px-6 py-2 rounded-lg">
                Save Rule
            </button>
        </div>

    </form>
</div>
</x-app-layout>
