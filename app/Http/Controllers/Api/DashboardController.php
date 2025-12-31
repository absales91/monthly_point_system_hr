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

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $user->name,
                'present' => $present,
                'half_day' => $halfDay,
                'absent' => $absent,
                'salary' => round($salary),
            ]
        ]);
    }
}
