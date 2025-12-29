<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('employees.index', [
            'employees' => User::whereIn('role', ['employee'])->get()
        ]);
    }

    public function create()
    {

        return view('employees.create');
    }

    public function store(Request $request)
    {
        abort_unless(isAdmin(), 403);

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:admin,manager,employee',
            'password' => 'required|string|min:6|confirmed',

            'basic_salary' => 'required|numeric|min:0',
            'working_days' => 'required|integer|min:1',

            'office_in_time' => 'required',
            'office_out_time' => 'required',
            'late_minutes_allowed' => 'required|integer|min:0',
            'half_day_hours' => 'required|integer|min:1',
        ]);

        $perDaySalary = $request->basic_salary / $request->working_days;

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
            // 'status'   => 1,
            // Salary
            'basic_salary' => $request->basic_salary,
            'working_days' => $request->working_days,
            'per_day_salary' => round($perDaySalary, 2),

            // Attendance rules
            'office_in_time' => $request->office_in_time,
            'office_out_time' => $request->office_out_time,
            'late_minutes_allowed' => $request->late_minutes_allowed,
            'half_day_hours' => $request->half_day_hours,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee created successfully');
    }

    public function salarySlipDownload($month, $year)
{
    /** @var \App\Models\User $user */
    $user = auth()->user();

    $attendances = Attendance::where('employee_id',$user->id)
        ->whereMonth('date',$month)
        ->whereYear('date',$year)
        ->get();

    $present = $attendances->where('status','present')->count();
    $halfDay = $attendances->where('status','half_day')->count();

    $payableDays = $present + ($halfDay * 0.5);
    $salary = $payableDays * $user->per_day_salary;

    $pdf = Pdf::loadView('employee.salary-slip', compact(
        'user',
        'present',
        'halfDay',
        'payableDays',
        'salary',
        'month',
        'year'
    ));

    return $pdf->download("salary-slip-$month-$year.pdf");
}
}
