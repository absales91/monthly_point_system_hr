<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * 🔁 SINGLE PUNCH API (IN / OUT)
     */
    public function punch(Request $request)
    {
        $request->validate([
            'image'     => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'latitude'  => 'required',
            'longitude' => 'required',
            'type'      => 'required|in:in,out',
        ]);

        $employeeId = $request->user()->id;
        $now        = Carbon::now('Asia/Kolkata');
        $today      = $now->toDateString();

        // 🔍 Last punch today
        $lastPunch = DB::table('attendance_logs')
            ->where('employee_id', $employeeId)
            ->where('date', $today)
            ->orderByDesc('id')
            ->first();

        // ❌ Prevent invalid sequences
        if (!$lastPunch && $request->type === 'out') {
            return response()->json([
                'success' => false,
                'message' => 'Punch IN required first',
            ], 400);
        }

        if ($lastPunch && $lastPunch->punch_type === $request->type) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid punch sequence',
            ], 400);
        }

        // 📸 Store image
        $image = $request->file('image');
        $filename = uniqid() . '_' . $image->getClientOriginalName();
        $destination = public_path('attendance');

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $image->move($destination, $filename);
        $imagePath = 'attendance/' . $filename;

        // Duplicate prevention (10 seconds)
        $recentPunch = DB::table('attendance_logs')
            ->where('employee_id', $employeeId)
            ->where('punch_type', $request->type)
            ->where('created_at', '>=', now()->subSeconds(10))
            ->exists();

        if ($recentPunch) {
            return response()->json([
                'success' => false,
                'message' => 'Punch already recorded. Please wait.',
            ], 429);
        }

        DB::transaction(function () use ($employeeId, $today, $now, $request, $imagePath) {

            DB::table('attendance_logs')->insert([
                'employee_id' => $employeeId,
                'date'        => $today,
                'punch_type'  => $request->type,
                'image'       => $imagePath,
                'latitude'    => $request->latitude,
                'longitude'   => $request->longitude,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            if ($request->type === 'out') {
                $this->calculateTodayAttendance($employeeId);
            }
            // Recalculate inside SAME transaction
        });


        return response()->json([
            'success'    => true,
            'message'    => strtoupper($request->type) . ' punch successful',
            'time'       => $now->format('H:i:s'),
            'last_punch' => $request->type,
        ]);
    }

    /**
     * ⏱ Calculate today's working minutes & status
     */
    // private function calculateTodayAttendance($employeeId)
    // {
    //     $today = Carbon::now('Asia/Kolkata')->toDateString();

    //     $user = DB::table('users')->where('id', $employeeId)->first();
    //     if (!$user) {
    //         throw new \Exception("User not found for attendance calculation.");
    //     }

    //     $defaults = $this->getOfficeDefaults();

    //     $officeInTime  = $user->office_in_time ?? $defaults['office_in'];
    //     $officeOutTime = $user->office_out_time ?? $defaults['office_out'];
    //     $halfDayHours  = $user->half_day_hours ?? $defaults['half_day_hours'];
    //     $lateGrace     = $user->late_minutes_allowed ?? $defaults['late_minutes_allowed'];

    //     $officeIn  = Carbon::parse($today . ' ' . $officeInTime);
    //     $officeOut = Carbon::parse($today . ' ' . $officeOutTime);

    //     $fullDayMinutes = $officeIn->diffInMinutes($officeOut);
    //     $halfDayMinutes = $halfDayHours * 60;

    //     $logs = DB::table('attendance_logs')
    //         ->where('employee_id', $employeeId)
    //         ->where('date', $today)
    //         ->orderBy('created_at')
    //         ->get();

    //     if ($logs->isEmpty()) {
    //         Attendance::updateOrCreate(
    //             ['employee_id' => $employeeId, 'date' => $today],
    //             [
    //                 'working_minutes' => 0,
    //                 'actual_minutes'  => 0,
    //                 'overtime_minutes' => 0,
    //                 'status' => 'absent'
    //             ]
    //         );
    //         return 0;
    //     }

    //     $firstInLog = $logs->firstWhere('punch_type', 'in');

    //     if (!$firstInLog) {
    //         Attendance::updateOrCreate(
    //             ['employee_id' => $employeeId, 'date' => $today],
    //             [
    //                 'working_minutes' => 0,
    //                 'actual_minutes'  => 0,
    //                 'overtime_minutes' => 0,
    //                 'status' => 'absent'
    //             ]
    //         );
    //         return 0;
    //     }

    //     // 🔁 Pair IN/OUT safely
    //     $totalMinutes = 0;
    //     $inTime = null;

    //     foreach ($logs as $log) {

    //         if ($log->punch_type === 'in') {
    //             $inTime = Carbon::parse($log->created_at);
    //         }

    //         if ($log->punch_type === 'out' && $inTime) {
    //             $outTime = Carbon::parse($log->created_at);

    //             if ($outTime->greaterThanOrEqualTo($inTime)) {
    //                 $totalMinutes += $inTime->diffInMinutes($outTime);
    //             }

    //             $inTime = null;
    //         }
    //     }

    //     // If still IN without OUT
    //     if ($inTime) {
    //         $outTime = Carbon::now('Asia/Kolkata');
    //         if ($outTime->greaterThan($officeOut)) {
    //             $outTime = $officeOut;
    //         }
    //         $totalMinutes += $inTime->diffInMinutes($outTime);
    //     }

    //     // 🔹 Late check
    //     $firstIn = Carbon::parse($firstInLog->created_at);
    //     $lateCutoff = $officeIn->copy()->addMinutes($lateGrace);
    //     $isLate = $firstIn->greaterThan($lateCutoff);

    //     // Preserve real total before capping
    //     $actualMinutes = $totalMinutes;
    //     $overtimeMinutes = $totalMinutes > $fullDayMinutes
    //         ? ($totalMinutes - $fullDayMinutes)
    //         : 0;

    //     // Cap for attendance status
    //     $totalMinutes = min($totalMinutes, $fullDayMinutes);

    //     // 🔹 Final status rules
    //     if ($totalMinutes >= $fullDayMinutes) {
    //         $status = $isLate ? 'late' : 'present';
    //     } elseif ($totalMinutes >= (4 * 60)) {
    //         $status = 'short_leave';
    //     } elseif ($totalMinutes >= (2 * 60)) {
    //         $status = 'half_day';
    //     } else {
    //         $status = 'absent';
    //     }

    //     Attendance::updateOrCreate(
    //         ['employee_id' => $employeeId, 'date' => $today],
    //         [
    //             'working_minutes'  => $totalMinutes,
    //             'actual_minutes'   => $actualMinutes,
    //             'overtime_minutes' => $overtimeMinutes,
    //             'status'           => $status,
    //             'is_late'          => ($status === 'present' && $isLate), // ✅ key logic
    //         ]
    //     );

    //     return $totalMinutes;
    // }
private function calculateTodayAttendance($employeeId)
{
    $today = Carbon::now('Asia/Kolkata')->toDateString();

    $user = DB::table('users')->where('id', $employeeId)->first();
    if (!$user) {
        throw new \Exception("User not found for attendance calculation.");
    }

    $defaults = $this->getOfficeDefaults();

    $officeInTime  = $user->office_in_time ?? $defaults['office_in']; // Default: 10:00 AM
    $officeOutTime = $user->office_out_time ?? $defaults['office_out']; // Default: 7:00 PM
    $halfDayHours  = $user->half_day_hours ?? $defaults['half_day_hours'];
    $lateGrace     = $user->late_minutes_allowed ?? $defaults['late_minutes_allowed'];

    // Shift timing
    $officeIn  = Carbon::parse($today . ' ' . $officeInTime); // 10:00 AM
    $officeOut = Carbon::parse($today . ' ' . $officeOutTime); // 7:00 PM

    $fullDayMinutes = $officeIn->diffInMinutes($officeOut); // 9 hours = 540 minutes
    $halfDayMinutes = $halfDayHours * 60; // e.g., 4 hours = 240 minutes

    // Get the attendance logs for today
    $logs = DB::table('attendance_logs')
        ->where('employee_id', $employeeId)
        ->where('date', $today)
        ->orderBy('created_at')
        ->get();

    if ($logs->isEmpty()) {
        Attendance::updateOrCreate(
            ['employee_id' => $employeeId, 'date' => $today],
            [
                'working_minutes' => 0,
                'actual_minutes'  => 0,
                'overtime_minutes' => 0,
                'status' => 'absent'
            ]
        );
        return 0;
    }

    $firstInLog = $logs->firstWhere('punch_type', 'in');

    if (!$firstInLog) {
        Attendance::updateOrCreate(
            ['employee_id' => $employeeId, 'date' => $today],
            [
                'working_minutes' => 0,
                'actual_minutes'  => 0,
                'overtime_minutes' => 0,
                'status' => 'absent'
            ]
        );
        return 0;
    }

    // 🔁 Pair IN/OUT safely
    $totalMinutes = 0;
    $inTime = null;

    foreach ($logs as $log) {
        if ($log->punch_type === 'in') {
            $inTime = Carbon::parse($log->created_at);
        }

        if ($log->punch_type === 'out' && $inTime) {
            $outTime = Carbon::parse($log->created_at);

            if ($outTime->greaterThanOrEqualTo($inTime)) {
                $totalMinutes += $inTime->diffInMinutes($outTime);
            }

            $inTime = null;
        }
    }

    // If still IN without OUT
    if ($inTime) {
        $outTime = Carbon::now('Asia/Kolkata');
        if ($outTime->greaterThan($officeOut)) {
            $outTime = $officeOut;
        }
        $totalMinutes += $inTime->diffInMinutes($outTime);
    }

    // 🔹 Late check
    $firstIn = Carbon::parse($firstInLog->created_at);
    $lateCutoff = $officeIn->copy()->addMinutes($lateGrace);
    $isLate = $firstIn->greaterThan($lateCutoff);

    // Preserve real total before capping
    $actualMinutes = $totalMinutes;
    $overtimeMinutes = $totalMinutes > $fullDayMinutes
        ? ($totalMinutes - $fullDayMinutes)
        : 0;

    // Cap for attendance status
    $totalMinutes = min($totalMinutes, $fullDayMinutes);

    // 🔹 Final status rules
    // Mark as "present" if the employee works the full shift time or more (even if late)
    if ($totalMinutes >= $fullDayMinutes) {
        $status = $isLate ? 'late' : 'present';
    } elseif ($totalMinutes >= (4 * 60)) {
        $status = 'short_leave';
    } elseif ($totalMinutes >= (2 * 60)) {
        $status = 'half_day';
    } else {
        $status = 'absent';
    }

    Attendance::updateOrCreate(
        ['employee_id' => $employeeId, 'date' => $today],
        [
            'working_minutes'  => $totalMinutes,
            'actual_minutes'   => $actualMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'status'           => $status,
            'is_late'          => ($status === 'present' && $isLate), // ✅ key logic
        ]
    );

    return $totalMinutes;
}

    /**
     * 📊 Monthly Attendance Summary
     */
    public function attendanceSummary(Request $request)
    {
        $employeeId = $request->user()->id;
        $month = $request->get('month', now()->format('Y-m'));

        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate   = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $records = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get()
             ->map(function ($row) {
                return [
                    'date' => Carbon::parse($row->date)->format('d M Y'),
                    'working_minutes' => (int) $row->working_minutes,
                    'status' => $row->status,
                ];
            });

        $summary = [
            'month' => $startDate->format('F Y'),
            'present' => $records->where('status', 'present')->count(),
            'half_day' => $records->where('status', 'half_day')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'short_leave' => $records->where('status', 'short_leave')->count(),
            'late' => $records->where('status', 'late')->count(),
            'overtime' => $records->sum('overtime_minutes')  ?? 0 ,
            'total_working_minutes' => $records->sum('working_minutes'),
            'total_overtime_minutes' => $records->sum('overtime_minutes'),
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'records' => $records,
        ]);
    }
    ///
        /**
     * 🔄 Get last punch (for Flutter button state)
     */
    public function lastPunch(Request $request)
    {
        $employeeId = $request->user()->id;
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        $lastPunch = DB::table('attendance_logs')
            ->where('employee_id', $employeeId)
            ->where('date', $today)
            ->orderByDesc('id')
            ->value('punch_type');

        return response()->json([
            'last_punch' => $lastPunch ?? 'none',
        ]);
    }

    public function punchesByDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $employeeId = $request->user()->id;
        $date = $request->date;

        $punches = DB::table('attendance_logs')
            ->where('employee_id', $employeeId)
            ->where('date', $date)
            ->orderBy('created_at')
            ->get()
            ->map(function ($row) {
                return [
                    'type' => $row->punch_type, // in / out
                    'time' => Carbon::parse($row->created_at)
                        // ->timezone('Asia/Kolkata')
                        ->format('h:i A'),
                    'image' => url($row->image),
                    'latitude' => $row->latitude,
                    'longitude' => $row->longitude,
                ];
            });

        return response()->json([
            'success' => true,
            'date' => $date,
            'total_punches' => $punches->count(),
            'punches' => $punches,
        ]);
    }


    

    private function getOfficeDefaults()
    {
        return [
            'office_in' => '10:00:00',
            'office_out' => '19:00:00',
            'half_day_hours' => 4,
            'late_minutes_allowed' => 15,
        ];
    }

public function dailyEmployeeAttendance(Request $request)
{
    $request->validate([
        'date' => 'nullable|date',
        'employee_id' => 'nullable|exists:users,id'
    ]);

    $date = $request->date
        ? Carbon::parse($request->date)->toDateString()
        : Carbon::today()->toDateString();

    /*
    |--------------------------------------------------------------------------
    | 1️⃣ Get Employees
    |--------------------------------------------------------------------------
    */
    $employeesQuery = User::where('role', 'employee');

    if ($request->employee_id) {
        $employeesQuery->where('id', $request->employee_id);
    }

    $employees = $employeesQuery->get();

    /*
    |--------------------------------------------------------------------------
    | 2️⃣ Get Worked Minutes from Attendances Table
    |--------------------------------------------------------------------------
    */
    $attendances = DB::table('attendances')
        ->select('employee_id', 'actual_minutes','status')
        ->whereDate('date', $date)
        ->get()
        ->keyBy('employee_id');

    /*
    |--------------------------------------------------------------------------
    | 3️⃣ Get Check-in & Check-out from Logs Table (Type Wise)
    |--------------------------------------------------------------------------
    */
    $logs = DB::table('attendance_logs')
        ->select(
            'employee_id',
            DB::raw("MIN(CASE WHEN punch_type = 'in' THEN created_at END) as check_in"),
            DB::raw("MAX(CASE WHEN punch_type = 'out' THEN created_at END) as check_out")
        )
        ->whereDate('created_at', $date)
        ->groupBy('employee_id')
        ->get()
        ->keyBy('employee_id');

    /*
    |--------------------------------------------------------------------------
    | 4️⃣ Prepare Summary
    |--------------------------------------------------------------------------
    */
    $present = 0;
    $absent = 0;
    $lessHours = 0;
    $overtime = 0;

    $staff = [];

    foreach ($employees as $employee) {

        if (isset($attendances[$employee->id]) && $attendances[$employee->id]->status !== 'absent') {

            $present++;

            $workedMinutes = $attendances[$employee->id]->actual_minutes ?? 0;

            $checkIn = isset($logs[$employee->id]->check_in)
                ? Carbon::parse($logs[$employee->id]->check_in)
                : null;

            $checkOut = isset($logs[$employee->id]->check_out)
                ? Carbon::parse($logs[$employee->id]->check_out)
                : null;

            // Convert minutes to HH:MM format
            $hoursWorked = gmdate("H:i", $workedMinutes * 60);

            // Less Hours (< 8 hours)
            if ($workedMinutes < 480) $lessHours++;

            // Overtime (> 9 hours)
            if ($workedMinutes > 540) $overtime++;

            $staff[] = [
                'id' => $employee->id,
                'name' => $employee->name,
                'status' => $attendances[$employee->id]->status,
                'punchIn' => $checkIn ? $checkIn->format('h:i A') : null,
                'punchOut' => $checkOut ? $checkOut->format('h:i A') : null,
                'hoursWorked' => $hoursWorked
            ];

        } else {

            $absent++;

            $staff[] = [
                'id' => $employee->id,
                'name' => $employee->name,
                'status' => 'absent',
                'punchIn' => null,
                'punchOut' => null,
                'hoursWorked' => "00:00"
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 5️⃣ Return Response (Flutter Compatible)
    |--------------------------------------------------------------------------
    */
    return response()->json([
        'status' => true,
        'date' => $date,
        'summary' => [
            'present' => $present,
            'half_day' => 0,
            'absent' => $absent,
            'paid_leave' => 0,
            'less_hours' => $lessHours,
            'overtime' => $overtime,
        ],
        'staff' => $staff
    ]);
}


}
