<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-8">

        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">📝 Task Details</h2>
                <p class="text-sm text-gray-500">Task overview & activity timeline</p>
            </div>

            <!-- Status -->
            <span class="px-4 py-1.5 rounded-full text-sm font-semibold tracking-wide
            @if($task->status == 'completed') bg-green-100 text-green-700
            @elseif($task->status == 'in_progress') bg-yellow-100 text-yellow-700
            @else bg-gray-100 text-gray-700 @endif">
                {{ ucfirst(str_replace('_',' ', $task->status)) }}
            </span>
        </div>

        <!-- Task Info Card -->
        <div class="bg-white shadow-sm rounded-xl p-6 grid md:grid-cols-2 gap-8">

            <div class="space-y-6">
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-400">Title</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $task->title }}</p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-400">Description</p>
                    <p class="text-gray-700 leading-relaxed">{{ $task->description }}</p>
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-400">Assigned Employee</p>
                    <p class="font-medium text-gray-800">{{ $task->employee->name }}</p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-400">Priority</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($task->priority == 'high') bg-red-100 text-red-700
                    @elseif($task->priority == 'medium') bg-yellow-100 text-yellow-700
                    @else bg-blue-100 text-blue-700 @endif">
                        {{ ucfirst($task->priority) }}
                    </span>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-400">Due Date</p>
                    <p class="font-medium text-gray-800">
                        {{ $task->due_date ? $task->due_date->format('d M Y') : '—' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Admin Update Box -->
        @if(auth()->user()->role === 'admin')
        <div class="bg-white shadow-sm rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                ➕ Add Task Update
            </h3>

            @if(session('success'))
            <div class="mb-4 text-green-700 bg-green-100 px-4 py-2 rounded-lg">
                {{ session('success') }}
            </div>
            @endif

            <form method="POST"
                action="{{ route('admin.tasks.logs.store', $task) }}"
                enctype="multipart/form-data"
                class="space-y-4">
                @csrf

                <textarea name="message"
                    rows="3"
                    required
                    class="w-full rounded-lg border-gray-300 focus:ring-indigo-500"
                    placeholder="Write task update..."></textarea>

                <div class="flex items-center justify-between">
                    <input type="file"
                        name="image"
                        accept="image/*"
                        class="text-sm text-gray-600">

                    <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg
                               hover:bg-indigo-700 transition shadow">
                        Save Update
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- Task Logs Timeline -->
        <div class="bg-white shadow-sm rounded-xl p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-6">
                📌 Task Activity
            </h3>

            @forelse($task->logs as $log)
          @php
    $isAdmin = ($log->employee->role === 'admin');
@endphp

<div class="w-full flex {{ $isAdmin ? 'justify-start' : 'justify-end' }}">

    <div class="max-w-3xl w-full bg-white rounded-xl border border-gray-100
                px-6 py-4 shadow-sm">

        <!-- Header -->
        <div class="flex items-center justify-between mb-2">

            <div class="flex items-center gap-3">

                <!-- Avatar -->
                <div style="width: 45px; height: 45px;" class="w-10 h-10 rounded-full
                    {{ $isAdmin ? 'bg-indigo-600' : 'bg-emerald-600' }}
                    flex items-center justify-center
                    text-white font-semibold text-sm">
                    {{ strtoupper(substr($log->employee->name ?? 'A', 0, 1)) }}
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $log->employee->name ?? 'Admin' }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ $log->created_at->format('d M Y, h:i A') }}
                    </p>
                </div>
            </div>

            <!-- Role Badge -->
            <span class="px-3 py-1 text-xs font-semibold rounded-full
                {{ $isAdmin
                    ? 'bg-indigo-50 text-indigo-600'
                    : 'bg-emerald-50 text-emerald-600' }}">
                {{ $isAdmin ? 'ADMIN' : 'EMPLOYEE' }}
            </span>
        </div>

        <!-- Message -->
        <p class="text-gray-800 leading-relaxed">
            {{ $log->note }}
        </p>

        <!-- Image -->
        @if($log->image)
            <div class="mt-4">
                <a href="{{ asset('storage/'.$log->image) }}" target="_blank">
                    <img
                        src="{{ asset('storage/'.$log->image) }}"
                        class="w-64 rounded-lg border shadow-sm hover:shadow-md transition"
                        alt="Task update image">
                </a>
            </div>
        @endif

    </div>
</div>



            @empty
            <p class="text-gray-500 text-center py-8">
                No task updates available.
            </p>
            @endforelse
        </div>

    </div>
</x-app-layout>