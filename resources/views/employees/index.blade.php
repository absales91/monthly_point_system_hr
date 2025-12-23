<div class="max-w-7xl mx-auto px-6 py-8">
<x-app-layout>
    
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Manage Employees</h2>
            <p class="text-sm text-gray-500">Create, update & manage employees</p>
        </div>

        {{-- Add Employee --}}
        <a href="/employees/create"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">
            ➕ Add Employee
        </a>
    </div>

    {{-- Employee Table --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Employee</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @foreach($employees as $emp)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $loop->iteration }}</td>

                    <td class="px-4 py-3">
                        <div class="font-semibold">{{ $emp->name }}</div>
                        <div class="text-xs text-gray-500">{{ $emp->email }}</div>
                    </td>

                    <td class="px-4 py-3 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            @if($emp->role === 'admin') bg-red-100 text-red-700
                            @elseif($emp->role === 'manager') bg-blue-100 text-blue-700
                            @else bg-green-100 text-green-700
                            @endif">
                            {{ ucfirst($emp->role) }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $emp->status ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                            {{ $emp->status ? 'Active' : 'Inactive' }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="/employees/{{ $emp->id }}/edit"
                           class="text-indigo-600 hover:underline text-sm">
                            Edit
                        </a>

                        <form action="/employees/{{ $emp->id }}/toggle" method="POST" class="inline">
                            @csrf
                            <button class="text-red-600 hover:underline text-sm">
                                {{ $emp->status ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
</x-app-layout>