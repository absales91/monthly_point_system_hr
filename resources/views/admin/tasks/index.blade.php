<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-6">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Task Management</h2>

            <a href="{{ route('admin.tasks.create') }}"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                + Create Task
            </a>
        </div>

        <div class="bg-white shadow rounded-xl overflow-hidden">
            <table class="min-w-full divide-y">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm">Title</th>
                        <th class="px-6 py-3 text-left text-sm">Employee</th>
                        <th class="px-6 py-3 text-left text-sm">Priority</th>
                        <th class="px-6 py-3 text-left text-sm">Status</th>
                        <th class="px-6 py-3 text-left text-sm">Due Date</th>
                        <th class="px-6 py-3 text-left text-sm">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @foreach($tasks as $task)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">{{ $task->title }}</td>
                        <td class="px-6 py-4">{{ $task->employee->name }}</td>

                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs
                        {{ $task->priority == 'high' ? 'bg-red-100 text-red-700' :
                           ($task->priority == 'medium' ? 'bg-yellow-100 text-yellow-700' :
                           'bg-green-100 text-green-700') }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700">
                                {{ ucfirst(str_replace('_',' ', $task->status)) }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-sm">
                            {{ $task->due_date ? $task->due_date->format('d M Y') : '-' }}
                        </td>
                        <td class="px-6 py-4">
    <div class="flex items-center gap-2 relative">

        <!-- View Button -->
        <a href="{{ route('admin.tasks.show', $task) }}"
           class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold
                  text-white bg-blue-600 rounded-lg shadow
                  hover:bg-blue-700 transition">
            👁 View
        </a>

        <!-- Dropdown -->
        @if(auth()->user()->role === 'admin')
       
<form action="{{ route('admin.tasks.destroy', $task) }}"
                      method="POST"
                      onsubmit="return confirm('Delete this task permanently? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="w-full text-left px-4 py-2 text-sm
                                   text-white hover:bg-red-50 rounded-lg bg-red-600 rounded-lg shadow
                  hover:bg-red-700 transition">
                        🗑 Delete Task
                    </button>
                </form>
           

            <!-- Menu -->
           
        @endif

    </div>
</td>


                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $tasks->links() }}
        </div>

    </div>
</x-app-layout>