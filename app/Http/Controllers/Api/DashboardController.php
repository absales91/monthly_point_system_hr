<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
         $user = $request->user(); // employee itself

        $month = Carbon::now()->month;
        $year  = Carbon::now()->year;
        $today = Carbon::today();

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

        // Salary calculation
        $perDaySalary = ($user->salary ?? 0) / 30;

        $salary =
            ($present * $perDaySalary) +
            ($halfDay * ($perDaySalary / 2));
         $attendance = Attendance::where('employee_id', $user->id)
        ->whereDate('date', $today)
        ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $user->name,
                'present' => $present,
                'half_day' => $halfDay,
                'absent' => $absent,
                'salary' => round($salary),
                'checked_in' => $attendance && $attendance->check_in ? true : false,
                'checked_out' => $attendance && $attendance->check_out ? true : false,
                 'today_status' => $attendance?->status ?? 'not_marked',
            ]
        ]);
    }
}
