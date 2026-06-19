<x-app-layout>
    <div class="max-w-xl mx-auto px-6 py-8">

        {{-- Header --}}
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit Employee</h2>
            <p class="text-sm text-gray-500">Update employee information</p>
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
        <form method="POST" action="{{ route('staff.update', $employee->id) }}"
            class="bg-white rounded-xl shadow p-6 space-y-5">
            @csrf

            {{-- Name --}}
            <div>
                <label class="block text-sm font-semibold text-gray-600">Full Name</label>
                <input type="text"
                    name="name"
                    value="{{ old('name', $employee->name) }}"
                    class="mt-1 w-full border rounded-lg px-4 py-2 focus:ring focus:ring-indigo-200"
                    required>
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-semibold text-gray-600">Email</label>
                <input type="email"
                    name="email"
                    value="{{ old('email', $employee->email) }}"
                    class="mt-1 w-full border rounded-lg px-4 py-2 focus:ring focus:ring-indigo-200"
                    required>
            </div>

            {{-- Role --}}
            <div>
                <label class="block text-sm font-semibold text-gray-600">Role</label>
                <select name="role" 
                    class="mt-1 w-full border rounded-lg px-4 py-2">
                    <option value="employee" @selected(old('role')==='employee' )>Employee</option>
                    <option value="manager" @selected(old('role')==='manager' )>Manager</option>
                    <option value="admin" @selected(old('role')==='admin' )>Admin</option>
                </select>
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-sm font-semibold text-gray-600">Password</label>
                <input type="password"
                    name="password"
                    class="mt-1 w-full border rounded-lg px-4 py-2"
                    >
            </div>

            {{-- Password Confirmation --}}
            <div>
                <label class="block text-sm font-semibold text-gray-600">
                    Confirm Password
                </label>
                <input type="password"
                    name="password_confirmation"
                    class="mt-1 w-full border rounded-lg px-4 py-2"
                    >
            </div>

            {{-- SALARY --}}
            <div class="bg-white  rounded shadow mb-4">
                <h3 class="font-semibold mb-2">Salary</h3>

                <input name="basic_salary" type="number"
                    placeholder="Monthly Salary" value="{{ old('basic_salary', $employee->basic_salary) }}"
                    class="w-full border rounded p-2 mb-2">

                <input name="working_days" value="{{ old('working_days', $employee->working_days) }}"
                    class="w-full border rounded p-2">
            </div>

            {{-- ATTENDANCE --}}
            <div class="bg-white  rounded shadow mb-4">
                <h3 class="font-semibold mb-2">Attendance Rules</h3>

                <div class="grid grid-cols-2 gap-2">
                    <label>Office In Time</label>
                    <input type="time" name="office_in_time" value="{{ old('office_in_time', $employee->office_in_time) }}"
                        class="border rounded p-2">
                    <label>Office Out Time</label>
                    <input type="time" name="office_out_time" value="{{ old('office_out_time', $employee->office_out_time) }}"
                        class="border rounded p-2">
                    <label>Late Minutes Allowed</label>
                    <input type="number" name="late_minutes_allowed" value="{{ old('late_minutes_allowed', $employee->late_minutes_allowed) }}"
                        class="border rounded p-2">
                    <label>Hours for Half Day</label>
                    <input type="number" name="half_day_hours" value="{{ old('half_day_hours', $employee->half_day_hours) }}"
                        class="border rounded p-2">
                </div>
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
                    Update Employee
                </button>
            </div>

        </form>
    </div>
</x-app-layout>