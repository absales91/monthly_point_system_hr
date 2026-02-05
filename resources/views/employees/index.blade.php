<x-app-layout>
<div class="max-w-7xl mx-auto px-6 py-8">

{{-- HEADER --}}
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Manage Employees</h2>
        <p class="text-sm text-gray-500">
            Create, update & manage employees
        </p>
    </div>

    <a href="{{ url('/employees/create') }}"
       class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 shadow">
        ➕ Add Employee
    </a>
</div>

{{-- SEARCH + FILTER BAR --}}
<div class="bg-white p-4 rounded-xl shadow mb-5 flex flex-wrap items-center gap-4">
    <input type="text"
           placeholder="Search by name or email..."
           class="border-gray-300 rounded-lg px-4 py-2 w-full md:w-1/3">

    <select class="border-gray-300 rounded-lg px-4 py-2">
        <option value="">All Roles</option>
        <option value="admin">Admin</option>
        <option value="manager">Manager</option>
        <option value="employee">Employee</option>
    </select>

    <select class="border-gray-300 rounded-lg px-4 py-2">
        <option value="">All Status</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
    </select>
</div>

{{-- EMPLOYEE TABLE CARD --}}
<div class="bg-white rounded-xl shadow overflow-hidden">

<table class="w-full text-sm">
<thead class="bg-gray-100 text-gray-700">
<tr>
    <th class="px-5 py-3 text-left">Employee</th>
    <th class="px-5 py-3 text-center">Role</th>
    <th class="px-5 py-3 text-center">Status</th>
    <th class="px-5 py-3 text-right">Actions</th>
</tr>
</thead>

<tbody class="divide-y">

@foreach($employees as $emp)
<tr class="hover:bg-gray-50">

{{-- EMPLOYEE --}}
<td class="px-5 py-4">
    <a href="{{ url('/staff/'.$emp->id) }}" class="block">
<div class="flex items-center gap-3">
    <div class="w-10 h-10 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-semibold">
        {{ strtoupper(substr($emp->name,0,2)) }}
    </div>

    <div>
        <p class="font-semibold text-gray-800">{{ $emp->name }}</p>
        <p class="text-xs text-gray-500">{{ $emp->email }}</p>
    </div>
</div>
</a>
</td>

{{-- ROLE --}}
<td class="px-5 py-4 text-center">
<span class="px-3 py-1 rounded-full text-xs font-semibold
@if($emp->role === 'admin') bg-red-100 text-red-700
@elseif($emp->role === 'manager') bg-blue-100 text-blue-700
@else bg-green-100 text-green-700
@endif">
{{ ucfirst($emp->role) }}
</span>
</td>

{{-- STATUS --}}
<td class="px-5 py-4 text-center">
<span class="px-3 py-1 rounded-full text-xs font-semibold
{{ $emp->status ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
{{ $emp->status ? 'Active' : 'Inactive' }}
</span>
</td>

{{-- ACTIONS --}}
<td class="px-5 py-4 text-right space-x-3">
<a href="{{ url('/employees/'.$emp->id.'/edit') }}"
   class="text-indigo-600 hover:underline text-sm font-medium">
✏ Edit
</a>

<form action="{{ url('/employees/'.$emp->id.'/toggle') }}"
      method="POST"
      class="inline">
@csrf
<button class="text-red-600 hover:underline text-sm font-medium">
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
