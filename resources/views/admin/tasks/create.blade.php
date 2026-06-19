<x-app-layout>
<div class="max-w-3xl mx-auto px-4 py-6">

    <h2 class="text-2xl font-semibold mb-6">Create Task</h2>
@if ($errors->any())
    <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <form method="POST" action="{{ route('admin.tasks.store') }}"
          class="bg-white shadow rounded-xl p-6 space-y-4">
        @csrf

        <div>
            <label class="text-sm font-medium">Task Title</label>
            <input type="text" name="title" required
                   class="w-full mt-1 border rounded-lg px-3 py-2">
        </div>

        <div>
            <label class="text-sm font-medium">Description</label>
            <textarea name="description"
                      class="w-full mt-1 border rounded-lg px-3 py-2"></textarea>
        </div>

        <div>
            <label class="text-sm font-medium">Assign To</label>
            <select name="assigned_to" required
                    class="w-full mt-1 border rounded-lg px-3 py-2">
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Priority</label>
                <select name="priority"
                        class="w-full mt-1 border rounded-lg px-3 py-2">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Due Date</label>
                <input type="date" name="due_date"
                       class="w-full mt-1 border rounded-lg px-3 py-2">
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700
                           text-white rounded-lg">
                Create Task
            </button>

            <a href="{{ route('admin.tasks.index') }}"
               class="ml-3 text-gray-600 hover:underline">
                Cancel
            </a>
        </div>
    </form>

</div>
</x-app-layout>
