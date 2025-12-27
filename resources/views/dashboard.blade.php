<x-app-layout>
<div class="max-w-7xl mx-auto px-6 py-8">

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

        {{-- Role Badge --}}
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

            <a href="/my-reports"
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

    {{-- ================= MANAGER VIEW ================= --}}
    @if(canManage() && !isAdmin())

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Assign Points --}}
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl shadow-lg p-6">
            <h4 class="text-lg font-semibold">Assign Monthly Points</h4>
            <p class="text-sm opacity-90 mt-1">
                Attendance, behaviour, decisions
            </p>

            <a href="{{ route('monthly-reports.create') }}"
               class="inline-block mt-6 bg-white text-blue-700 px-4 py-2 rounded-lg text-sm font-semibold">
                Add Report →
            </a>
        </div>

        {{-- My Reports --}}
        <div class="bg-white rounded-2xl shadow p-6">
            <h4 class="font-semibold text-gray-700">My Reports</h4>
            <p class="text-sm text-gray-500">
                View your own monthly reports
            </p>

            <a href="/my-report"
               class="inline-block mt-4 text-blue-600 text-sm font-semibold hover:underline">
                View →
            </a>
        </div>

        {{-- Manager Info --}}
        <div class="bg-white rounded-2xl shadow p-6">
            <h4 class="font-semibold text-gray-700 mb-2">Manager Privileges</h4>
            <ul class="text-sm text-gray-500 space-y-1">
                <li>✔ Assign points</li>
                <li>✔ Behaviour & decision scoring</li>
                <li>❌ Manage employees</li>
            </ul>
        </div>

    </div>

    @endif

    {{-- ================= ADMIN VIEW ================= --}}
    {{-- ================= ADMIN VIEW ================= --}}
@if(isAdmin())

<div class="grid grid-cols-1 md:grid-cols-4 gap-6">

    {{-- Monthly Reports --}}
    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-2xl shadow-lg p-6">
        <h4 class="text-lg font-semibold">Monthly Reports</h4>
        <p class="text-sm opacity-90 mt-1">
            Assign and manage performance
        </p>

        <a href="{{ route('monthly-reports.create') }}"
           class="inline-block mt-6 bg-white text-indigo-700 px-4 py-2 rounded-lg text-sm font-semibold">
            Add Report →
        </a>
    </div>

    {{-- Manage Employees --}}
    <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-2xl shadow-lg p-6">
        <h4 class="text-lg font-semibold">Manage Employees</h4>
        <p class="text-sm opacity-90 mt-1">
            Roles, status & access
        </p>

        <a href="/employees"
           class="inline-block mt-6 bg-white text-red-700 px-4 py-2 rounded-lg text-sm font-semibold">
            Manage →
        </a>
    </div>

    {{-- Point Rules --}}
    <div class="bg-gradient-to-br from-gray-700 to-gray-800 text-white rounded-2xl shadow-lg p-6">
        <h4 class="text-lg font-semibold">Point Rules</h4>
        <p class="text-sm opacity-90 mt-1">
            Scoring logic control
        </p>

        <a href="/point-rules"
           class="inline-block mt-6 bg-white text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold">
            Configure →
        </a>
    </div>

    {{-- Reward Rules --}}
<div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl shadow-lg p-6">
    <h4 class="text-lg font-semibold">Reward Rules</h4>
    <p class="text-sm opacity-90 mt-1">
        Points → Paid Leave / Bonus logic
    </p>

    <a href="{{ route('reward-rules.index') }}"
       class="inline-block mt-6 bg-white text-purple-700 px-4 py-2 rounded-lg text-sm font-semibold">
        Configure →
    </a>
</div>


    {{-- ✅ NEW: View All Reports --}}
    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-2xl shadow-lg p-6">
        <h4 class="text-lg font-semibold">All Employee Reports</h4>
        <p class="text-sm opacity-90 mt-1">
            View month-wise performance
        </p>

        <a href="{{ route('monthly-reports-all.index') }}"
           class="inline-block mt-6 bg-white text-emerald-700 px-4 py-2 rounded-lg text-sm font-semibold">
            View Reports →
        </a>
    </div>

    {{-- Generate Rewards --}}
<div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-2xl shadow-lg p-6">
    <h4 class="text-lg font-semibold">Generate Rewards</h4>
    <p class="text-sm opacity-90 mt-1">
        Convert monthly points into rewards
    </p>

    <form method="POST"
          action="{{ route('reward.generate') }}"
          class="mt-6 flex items-center gap-3">
        @csrf

        <input type="month"
               name="month"
               required
               class="rounded-lg px-3 py-2 text-sm text-gray-800">

        <button type="submit"
                class="bg-white text-orange-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-orange-100">
            Generate →
        </button>
    </form>
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
