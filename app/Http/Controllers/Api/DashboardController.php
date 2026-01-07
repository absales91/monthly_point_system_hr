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

    // 📊 Attendance counts (from attendances table)
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

    // 💰 Salary calculation (policy-based)
    $perDaySalary = (float) ($user->per_day_salary ?? 0);

    $salary =
        ($present * $perDaySalary) +
        ($halfDay * ($perDaySalary / 2));

    // 📌 Today status (from attendances)
    $todayAttendance = Attendance::where('employee_id', $user->id)
        ->whereDate('date', $today)
        ->first();

    // 🔁 Last punch (from attendance_logs)
    $lastPunch = DB::table('attendance_logs')
        ->where('employee_id', $user->id)
        ->where('date', $today)
        ->orderByDesc('id')
        ->value('punch_type');

    return response()->json([
        'success' => true,
        'data' => [
            'name' => $user->name,

            // Summary
            'present' => $present,
            'half_day' => $halfDay,
            'absent' => $absent,

            // Salary
            'salary' => round($salary),

            // Today
            'today_status' => $todayAttendance?->status ?? 'not_marked',

            // Punch control (IMPORTANT for Flutter)
            'last_punch' => $lastPunch ?? 'none',
        ],
    ]);
}

}
