<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\EmployeeOfMonth;
use App\Models\EmployeeReward;
use App\Models\RewardWallet;

class DashboardController extends Controller
{
    /**
     * Entry dashboard (router decides role)
     */
    public function index()
    {
        $user = auth()->user();

        if (in_array($user->role, ['admin','manager'])) {
            return $this->admin();
        }

        return $this->employee();
    }

    /**
     * Admin / Manager dashboard
     */
    public function admin()
    {
        return view('dashboard', [
            'employeeOfMonths' => EmployeeOfMonth::with('employee')
                ->orderBy('month', 'desc')
                ->take(6)
                ->get(),
        ]);
    }

    /**
     * Employee dashboard
     */
    public function employee()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $month = now()->month;
        $year  = now()->year;

        $attendances = Attendance::where('employee_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $present = $attendances->where('status','present')->count();
        $halfDay = $attendances->where('status','half_day')->count();
        $absent  = $attendances->where('status','absent')->count();

        $payableDays = $present + ($halfDay * 0.5);
        $salary = $payableDays * ($user->per_day_salary ?? 0);

        return view('employee.dashboard', [
            // 🎖 Employee of Month
            'employeeOfMonths' => EmployeeOfMonth::with('employee')
                ->orderBy('month', 'desc')
                ->take(6)
                ->get(),

            // 🎁 Rewards
            'myWallet' => RewardWallet::where('employee_id', $user->id)->first(),
            'myRewards' => EmployeeReward::with('rule')
                ->where('employee_id', $user->id)
                ->latest()
                ->take(5)
                ->get(),

            // 💰 Salary
            'salary' => round($salary, 2),
            'payableDays' => $payableDays,

            // 📅 Attendance
            'month' => $month,
            'year' => $year,
            'present' => $present,
            'halfDay' => $halfDay,
            'absent' => $absent,
        ]);
    }
}
