<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /* ================= ADMIN ================= */

    public function index()
    {
        $employees = Employee::all();
        $date = now()->toDateString();

        $records = Attendance::with('employee')
            ->where('date', $date)
            ->get();

        return view('attendance.admin.index', compact(
            'employees',
            'records',
            'date'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'status'      => 'required'
        ]);

        Attendance::updateOrCreate(
            [
                'employee_id' => $request->employee_id,
                'date'        => $request->date
            ],
            [
                'status' => $request->status
            ]
        );

        return back()->with('success', 'Attendance saved');
    }

    /* ================= EMPLOYEE ================= */

    public function myAttendance()
    {
        $records = Attendance::with('employee')
            ->where('employee_id', auth()->user()->employee->id)
            ->orderByDesc('date')
            ->get();

        return view('attendance.employee.my', compact('records'));
    }

    public function checkIn()
    {
        Attendance::firstOrCreate(
            [
                'employee_id' => auth()->user()->employee->id,
                'date'        => now()->toDateString()
            ],
            [
                'check_in' => now(),
                'status'   => 'present'
            ]
        );

        return back()->with('success', 'Checked In');
    }

    public function checkOut()
    {
        $attendance = Attendance::where('employee_id', auth()->user()->employee->id)
            ->where('date', now()->toDateString())
            ->first();

        if ($attendance && !$attendance->check_out) {

            $attendance->check_out = now();

            $attendance->working_minutes =
                Carbon::parse($attendance->check_in)
                    ->diffInMinutes(now());

            $attendance->status = match (true) {
                $attendance->working_minutes >= 480 => 'present',
                $attendance->working_minutes >= 240 => 'half_day',
                default => 'absent',
            };

            $attendance->save();
        }

        return back()->with('success', 'Checked Out');
    }
}
