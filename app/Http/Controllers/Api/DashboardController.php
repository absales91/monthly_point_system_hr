<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
   public function index(Request $request)
{
    $user = $request->user();

    $now   = Carbon::now('Asia/Kolkata');
    $month = $now->month;
    $year  = $now->year;
    $today = $now->toDateString();

    // 📊 Attendance counts (monthly)
    $present = Attendance::where('employee_id', $user->id)
        ->whereMonth('date', $month)
        ->whereYear('date', $year)
        ->where('status', 'present')
        ->count();

    $halfDay = Attendance::where('employee_id', $user->id)
        ->whereMonth('date', $month)
        ->whereYear('date', $year)
        ->where('status', 'half_day')
        ->count();

    $absent = Attendance::where('employee_id', $user->id)
        ->whereMonth('date', $month)
        ->whereYear('date', $year)
        ->where('status', 'absent')
        ->count();

    // 💰 PER DAY SALARY (AUTO DERIVED)
    $perDaySalary = $user->per_day_salary;

    if (!$perDaySalary || $perDaySalary <= 0) {
        $perDaySalary = round(
            ($user->basic_salary ?? 0) / max($user->working_days, 1),
            2
        );
    }

    // 💵 Salary calculation (policy-based)
    $salary =
        ($present * $perDaySalary) +
        ($halfDay * ($perDaySalary / 2));

    // 📌 Today attendance status
    $todayAttendance = Attendance::where('employee_id', $user->id)
        ->whereDate('date', $today)
        ->first();

    // 🔁 Last punch (controls punch button)
    $lastPunch = DB::table('attendance_logs')
        ->where('employee_id', $user->id)
        ->where('date', $today)
        ->orderByDesc('id')
        ->value('punch_type');

    return response()->json([
        'success' => true,
        'data' => [
            'name' => $user->name,

            // Monthly summary
            'present' => $present,
            'half_day' => $halfDay,
            'absent' => $absent,

            // Salary
            'salary' => round($salary, 2),
            'per_day_salary' => $perDaySalary,

            // Today
            'today_status' => $todayAttendance?->status ?? 'not_marked',

            // Punch system
            'last_punch' => $lastPunch ?? 'none',
        ],
    ]);
}


}
