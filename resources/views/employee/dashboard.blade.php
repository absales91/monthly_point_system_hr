<x-app-layout>
    <div class="max-w-7xl mx-auto px-6 py-8">

        {{-- HEADER --}}
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">
                    Hello, {{ auth()->user()->name }} 👋
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Welcome to Employee Performance Dashboard
                </p>
            </div>

            <span class="mt-4 md:mt-0 inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold
        @if(isAdmin()) bg-red-100 text-red-700
        @elseif(canManage()) bg-blue-100 text-blue-700
        @else bg-green-100 text-green-700
        @endif">
                {{ strtoupper(auth()->user()->role) }}
            </span>
        </div>


        {{-- ================= EMPLOYEE VIEW ================= --}}
        @if(auth()->user()->role === 'employee')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-green-100 p-4 rounded shadow">
                <h3 class="text-sm text-gray-600">Present Days</h3>
                <p class="text-2xl font-bold text-green-700">{{ $present }}</p>
            </div>

            <div class="bg-yellow-100 p-4 rounded shadow">
                <h3 class="text-sm text-gray-600">Half Days</h3>
                <p class="text-2xl font-bold text-yellow-700">{{ $halfDay }}</p>
            </div>

            <div class="bg-red-100 p-4 rounded shadow">
                <h3 class="text-sm text-gray-600">Absent Days</h3>
                <p class="text-2xl font-bold text-red-700">{{ $absent }}</p>
            </div>
        </div>

        {{-- SALARY OVERVIEW --}}
        <div class="bg-white rounded shadow p-6 mb-6">
            <h3 class="font-semibold mb-2">💰 Salary Overview</h3>

            <p>Payable Days: <strong>{{ $payableDays }}</strong></p>
            <p>Per Day Salary: ₹{{ auth()->user()->per_day_salary }}</p>

            <hr class="my-2">

            <p class="text-xl font-bold">
                Net Salary: ₹{{ number_format($salary,2) }}
            </p>
        </div>

        <a href="{{ route('salary-slip.download',[$month,$year]) }}"
            class="inline-block bg-indigo-600 text-white px-5 py-2 rounded mb-10">
            ⬇ Download Salary Slip (PDF)
        </a>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- My Wallet --}}
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-2xl shadow-lg p-6">
                <h4 class="text-lg font-semibold">My Wallet 💼</h4>
                <p class="text-sm opacity-90 mt-1">
                    Reward points balance
                </p>

                <div class="mt-5 space-y-2">
                    <p class="text-sm">
                        <strong>Available Points:</strong>
                        {{ $myWallet->available_points ?? 0 }}
                    </p>

                    <p class="text-sm">
                        <strong>Lifetime Points:</strong>
                        {{ $myWallet->lifetime_points ?? 0 }}
                    </p>
                </div>
            </div>


            {{-- My Report --}}
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl shadow-lg p-6">
                <h4 class="text-lg font-semibold">My Monthly Reports</h4>
                <p class="text-sm opacity-90 mt-1">
                    View your performance and ratings
                </p>

                <a href="/my-report"
                    class="inline-block mt-6 bg-white text-green-700 px-4 py-2 rounded-lg text-sm font-semibold">
                    View My Report →
                </a>
            </div>

            {{-- Info Card --}}
            <div class="bg-white rounded-2xl shadow p-6">
                <h4 class="font-semibold text-gray-700 mb-2">Your Access</h4>
                <ul class="text-sm text-gray-500 space-y-1">
                    <li>✔ View monthly performance</li>
                    <li>✔ Track progress</li>
                    <li>❌ Edit scores</li>
                </ul>
            </div>
            {{-- My Rewards --}}
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl shadow-lg p-6">
                <h4 class="text-lg font-semibold">My Rewards 🎁</h4>
                <p class="text-sm opacity-90 mt-1">
                    Paid leaves, bonuses & badges earned
                </p>

                <a href="#my-rewards"
                    class="inline-block mt-6 bg-white text-purple-700 px-4 py-2 rounded-lg text-sm font-semibold">
                    View Rewards →
                </a>
            </div>


        </div>

        @endif


        {{-- ================= EMPLOYEE OF THE MONTH ================= --}}
        <div class="mt-12">

            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-800">
                    🏆 Employee of the Month
                </h3>

                {{-- Announce Button (Admin + Manager only) --}}
                @if(canManage())
                <form method="POST" action="{{ route('employee-of-month.announce') }}"
                    class="flex items-center gap-3">
                    @csrf
                    <input type="month" name="month"
                        class="border rounded-lg px-3 py-2 text-sm"
                        required>

                    <button
                        class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                        Announce
                    </button>
                </form>
                @endif
            </div>

            {{-- Winner Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                @forelse($employeeOfMonths ?? [] as $winner)
                <div class="bg-gradient-to-br from-yellow-400 to-amber-500 text-white rounded-2xl shadow-lg p-6">
                    <h4 class="text-lg font-semibold">
                        {{ \Carbon\Carbon::parse($winner->month)->format('F Y') }}
                    </h4>

                    <p class="mt-2 text-xl font-bold">
                        🎉 {{ $winner->employee->name }}
                    </p>

                    <p class="mt-1 text-sm opacity-90">
                        Score: {{ $winner->points }}/100
                    </p>
                </div>
                @empty
                <div class="col-span-full bg-white rounded-xl shadow p-6 text-gray-500">
                    No Employee of the Month announced yet.
                </div>
                @endforelse

            </div>
        </div>

    </div>
</x-app-layout>