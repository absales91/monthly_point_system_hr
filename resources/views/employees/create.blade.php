<x-app-layout>
<div class="max-w-xl mx-auto px-6 py-8">

    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Create Employee</h2>
        <p class="text-sm text-gray-500">Add new employee to the system</p>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg p-4">
            <ul class="text-sm list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form --}}
    <form method="POST" action="{{ route('employees.store') }}"
          class="bg-white rounded-xl shadow p-6 space-y-5">
        @csrf

        {{-- Name --}}
        <div>
            <label class="block text-sm font-semibold text-gray-600">Full Name</label>
            <input type="text"
                   name="name"
                   value="{{ old('name') }}"
                   class="mt-1 w-full border rounded-lg px-4 py-2 focus:ring focus:ring-indigo-200"
                   required>
        </div>

        {{-- Email --}}
        <div>
            <label class="block text-sm font-semibold text-gray-600">Email</label>
            <input type="email"
                   name="email"
                   value="{{ old('email') }}"
                   class="mt-1 w-full border rounded-lg px-4 py-2 focus:ring focus:ring-indigo-200"
                   required>
        </div>

        {{-- Role --}}
        <div>
            <label class="block text-sm font-semibold text-gray-600">Role</label>
            <select name="role"
                    class="mt-1 w-full border rounded-lg px-4 py-2">
                <option value="employee" @selected(old('role') === 'employee')>Employee</option>
                <option value="manager" @selected(old('role') === 'manager')>Manager</option>
                <option value="admin" @selected(old('role') === 'admin')>Admin</option>
            </select>
        </div>

        {{-- Password --}}
        <div>
            <label class="block text-sm font-semibold text-gray-600">Password</label>
            <input type="password"
                   name="password"
                   class="mt-1 w-full border rounded-lg px-4 py-2"
                   required>
        </div>

        {{-- Password Confirmation --}}
        <div>
            <label class="block text-sm font-semibold text-gray-600">
                Confirm Password
            </label>
            <input type="password"
                   name="password_confirmation"
                   class="mt-1 w-full border rounded-lg px-4 py-2"
                   required>
        </div>

        {{-- Actions --}}
        <div class="flex justify-between items-center pt-4">
            <a href="{{ route('employees.index') }}"
               class="text-sm text-gray-500 hover:underline">
                ← Back
            </a>

            <button
                type="submit"
                class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700">
                Create Employee
            </button>
        </div>

    </form>
</div>
</x-app-layout>
