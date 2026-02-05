<x-app-layout>
    <div class="max-w-7xl mx-auto px-6 py-6">

        {{-- BACK BUTTON --}}
        <div class="mb-4">
            <a href="{{ route('employees.index') }}" class="text-blue-600 font-medium flex items-center gap-1">
                ← Back
            </a>
        </div>

        {{-- BLUE HEADER CARD --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 flex justify-between items-center mb-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-200 text-blue-800 rounded-full flex items-center justify-center font-bold text-lg">
                    {{ strtoupper(substr($staff->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">{{ $staff->name }}</h2>
                    <p class="text-sm text-gray-600">
                        {{ ucfirst($staff->staff_type ?? 'Employee') }} (Monthly)
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6" x-data="{ tab: 'profile' }">

            {{-- LEFT SIDEBAR --}}
            <div class="col-span-3 bg-white rounded-xl shadow p-4 space-y-2">

                <button @click="tab='profile'"
                    class="w-full text-left px-3 py-2 rounded-lg"
                    :class="tab==='profile' ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-gray-100'">
                    Profile
                </button>

                <button @click="tab='attendance'"
                    class="w-full text-left px-3 py-2 rounded-lg"
                    :class="tab==='attendance' ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-gray-100'">
                    Attendance
                </button>

                <button @click="tab='salary'"
                    class="w-full text-left px-3 py-2 rounded-lg"
                    :class="tab==='salary' ? 'bg-blue-100 text-blue-700 font-semibold' : 'hover:bg-gray-100'">
                    Salary Overview
                </button>

            </div>

            {{-- RIGHT CONTENT --}}
            <div class="col-span-9 space-y-4">

                {{-- PROFILE TAB --}}
                {{-- PROFILE TAB --}}
                <div x-show="tab === 'profile'" class="space-y-4">

                    {{-- MAIN PROFILE CARD --}}
                    <div class="bg-white rounded-xl shadow p-6">

                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-semibold text-lg">Profile Information</h3>
                            <button class="border px-4 py-2 rounded-lg text-sm text-blue-600 hover:bg-blue-50">
                                ✏ Edit Profile
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                            {{-- LEFT: Avatar + Basic --}}
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-blue-200 text-blue-800 rounded-full flex items-center justify-center font-bold text-xl">
                                    {{ strtoupper(substr($staff->name, 0, 2)) }}
                                </div>

                                <div>
                                    <p class="font-semibold text-gray-800 text-lg">{{ $staff->name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ ucfirst($staff->staff_type ?? 'Employee') }}
                                    </p>
                                </div>
                            </div>

                            {{-- CENTER: Employment Info --}}
                            <div>
                                <p class="text-xs text-gray-500">Employee ID</p>
                                <p class="font-medium mb-3">{{ $staff->id ?? '-' }}</p>

                                <p class="text-xs text-gray-500">Designation</p>
                                <p class="font-medium">{{ $staff->designation ?? '-' }}</p>
                            </div>

                            {{-- RIGHT: Status --}}
                            <div>
                                <p class="text-xs text-gray-500">Status</p>
                                <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                    Active
                                </span>
                            </div>

                        </div>

                        <hr class="my-5">

                        {{-- DETAILS GRID --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">

                            <div>
                                <p class="text-gray-500">Email</p>
                                <p class="font-medium">{{ $staff->email ?? '-' }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Department</p>
                                <p class="font-medium">{{ $staff->department ?? '-' }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Reporting Manager</p>
                                <p class="font-medium">-</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Attendance Supervisor</p>
                                <p class="font-medium">-</p>
                            </div>

                        </div>

                    </div>

                </div> {{-- END PROFILE TAB --}}


                {{-- ATTENDANCE TAB --}}
                <div x-show="tab === 'attendance'">
                    <livewire:employee-attendance-tab :employee-id="$staff->id" />
                </div>
                {{-- SALARY OVERVIEW TAB --}}


                {{-- SALARY OVERVIEW TAB --}}
                <div x-show="tab === 'salary'" class="space-y-4">

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">Salary Overview</h3>

                        <div class="flex gap-3">


                            

                            <livewire:generate-salary-modal :employee-id="$staff->id" />

                        </div>
                    </div>

                    <div class="space-y-3">

                        @forelse($salaries as $salary)

                        <div class="bg-white rounded-xl shadow p-5 flex justify-between items-center">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-blue-100 text-blue-700 rounded-lg flex items-center justify-center font-bold">
                                    💼
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">
                                        {{ \Carbon\Carbon::parse($salary->month . '-01')->format('F Y') }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Duration:
                                        {{ \Carbon\Carbon::parse($salary->month . '-01')->startOfMonth()->format('d F Y') }} -
                                        {{ \Carbon\Carbon::parse($salary->month . '-01')->endOfMonth()->format('d F Y') }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-right">
                                <p class="text-sm text-gray-500">Due Amount</p>
                                <p class="font-bold text-lg">₹ {{ number_format($salary->net_salary, 2) }}</p>
                            </div>

                            <span class="text-gray-400">›</span>
                        </div>

                        @empty
                        <p class="text-gray-500">No salary records found.</p>
                        @endforelse

                    </div>
                </div>





            </div>

        </div>

    </div>
</x-app-layout>