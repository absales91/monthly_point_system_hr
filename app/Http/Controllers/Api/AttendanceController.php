<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    private function getIstDayRangeUtc($date = null)
    {
        $ist = $date
            ? Carbon::parse($date, 'Asia/Kolkata')
            : Carbon::now('Asia/Kolkata');

        return [
            $ist->copy()->startOfDay()->timezone('UTC'),
            $ist->copy()->endOfDay()->timezone('UTC'),
        ];
    }
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
        $now = Carbon::now(); // UTC

        // ✅ Get IST "today" range in UTC
        [$startUtc, $endUtc] = $this->getIstDayRangeUtc();

        DB::beginTransaction();

        try {
            DB::table('users')->where('id', $employeeId)->lockForUpdate()->first();
            // 🔍 Last punch today
            $lastPunch = DB::table('attendance_logs')
                ->where('employee_id', $employeeId)
                ->whereBetween('created_at', [$startUtc, $endUtc])
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            // ❌ Prevent invalid sequences
            if (!$lastPunch && $request->type === 'out') {
                DB::rollBack(); // ✅ MUST DO THIS
                return response()->json([
                    'success' => false,
                    'message' => 'Punch IN required first',
                ], 400);
            }

            if ($lastPunch && $lastPunch->punch_type === $request->type) {
                DB::rollBack(); // ✅ MUST DO THIS    
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid punch sequence',
                ], 400);
            }

            // 📸 Store image

            // Duplicate prevention (10 seconds)
            $recentPunch = DB::table('attendance_logs')
                ->where('employee_id', $employeeId)
                ->where('punch_type', $request->type)
                ->where('created_at', '>=', $now->copy()->subSeconds(10))
                ->exists();

            if ($recentPunch) {
                DB::rollBack(); // ✅ MUST DO THIS
                return response()->json([
                    'success' => false,
                    'message' => 'Punch already recorded. Please wait.',
                ], 429);
            }
            $image = $request->file('image');
            $filename = uniqid() . '_' . $image->getClientOriginalName();
            $destination = public_path('attendance');

            if (!is_dir($destination)) {
                mkdir($destination, 0755, true);
            }

            $image->move($destination, $filename);
            $imagePath = 'attendance/' . $filename;


            DB::table('attendance_logs')->insert([
                'employee_id' => $employeeId,
                'date'        => $now->copy()->timezone('Asia/Kolkata')->toDateString(),
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


            DB::commit();
            return response()->json([
                'success'    => true,
                'message'    => strtoupper($request->type) . ' punch successful',
                'time'       => $now->copy()->timezone('Asia/Kolkata')->format('H:i:s'),
                'last_punch' => $request->type,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Punch failed: ' . $e->getMessage(),
            ], 500);
        }
    }


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

        [$startUtc, $endUtc] = $this->getIstDayRangeUtc();


        $logs = DB::table('attendance_logs')
            ->where('employee_id', $employeeId)
            ->whereBetween('created_at', [$startUtc, $endUtc])
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

                if (!$inTime) {
                    $inTime = Carbon::parse($log->created_at)->timezone('Asia/Kolkata');
                }
            }

            if ($log->punch_type === 'out' && $inTime) {

                $outTime = Carbon::parse($log->created_at)->timezone('Asia/Kolkata');

                if ($outTime->greaterThanOrEqualTo($inTime)) {
                    $totalMinutes += $inTime->diffInMinutes($outTime);
                }

                $inTime = null;
            }
        }

        if ($inTime) {

           $outTime = now('Asia/Kolkata')->min($officeOut);

            if ($outTime->greaterThan($officeOut)) {
                $outTime = $officeOut;
            }

            $totalMinutes += $inTime->diffInMinutes($outTime);
        }

        // 🔹 Late check
        $firstIn = Carbon::parse($firstInLog->created_at)->timezone('Asia/Kolkata');
        $lateCutoff = $officeIn->copy()->addMinutes($lateGrace);
        $isLate = $firstIn->greaterThan($lateCutoff);

        // Preserve real total before capping
        $actualMinutes = $totalMinutes;
        $overtimeMinutes = $totalMinutes > $fullDayMinutes
            ? ($totalMinutes - $fullDayMinutes)
            : 0;

        // Cap for attendance status
        $totalMinutes = min($totalMinutes, $fullDayMinutes);

        // 🔹 Final status rules - Dynamic based on shift time
        // Present: >= 90% of expected shift
        $fullDayThreshold = $fullDayMinutes * 0.9;
        // Half-day: >= 50% of expected shift (or half_day_hours config, whichever is lower)
        $halfDayThreshold = min($fullDayMinutes * 0.5, $halfDayMinutes);
        $shortLeaveThreshold = $fullDayMinutes * 0.75; // 75%

        if ($totalMinutes >= $fullDayThreshold) {
            $status = 'present';
        } elseif ($totalMinutes >= $shortLeaveThreshold) {
            $status = 'short_leave';
        } elseif ($totalMinutes >= $halfDayThreshold) {
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
                'is_late'          =>  $isLate, // ✅ key logic
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
            'overtime' => $records->sum('overtime_minutes')  ?? 0,
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
                        ->timezone('Asia/Kolkata')
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
            ->select('employee_id', 'actual_minutes', 'status')
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
            ->whereDate(DB::raw("CONVERT_TZ(created_at,'+00:00','+05:30')"), $date)
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
                    ? Carbon::parse($logs[$employee->id]->check_in)->timezone('Asia/Kolkata')
                    : null;

                $checkOut = isset($logs[$employee->id]->check_out)
                    ? Carbon::parse($logs[$employee->id]->check_out)->timezone('Asia/Kolkata')
                    : null;

                // Convert minutes to HH:MM format
                $hoursWorked = gmdate("H:i", $workedMinutes * 60);

                // Get employee's shift time
                $empOfficeIn = $employee->office_in_time ?: $this->getOfficeDefaults()['office_in'];
                $empOfficeOut = $employee->office_out_time ?: $this->getOfficeDefaults()['office_out'];
                $empOfficeInParsed = Carbon::parse($date . ' ' . $empOfficeIn);
                $empOfficeOutParsed = Carbon::parse($date . ' ' . $empOfficeOut);
                $expectedShiftMinutes = $empOfficeInParsed->diffInMinutes($empOfficeOutParsed);

                // Less Hours (< 90% of expected shift)
                $lessHoursThreshold = $expectedShiftMinutes * 0.9;
                if ($workedMinutes < $lessHoursThreshold) $lessHours++;

                // Overtime (> expected shift)
                if ($workedMinutes > $expectedShiftMinutes) $overtime++;

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


    public function employeeMonthlyAttendance(Request $request)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
            'employee_id' => 'required|exists:users,id'
        ]);

        /*
    |--------------------------------------------------------------------------
    | 1️⃣ Get Employee
    |--------------------------------------------------------------------------
    */

        $user = User::where('id', $request->employee_id)
            ->where('role', 'employee')
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        /*
    |--------------------------------------------------------------------------
    | 2️⃣ Prepare Month Range
    |--------------------------------------------------------------------------
    */

        $month = $request->month
            ? Carbon::createFromFormat('Y-m', $request->month)
            : Carbon::now();

        $startDate = $month->copy()->startOfMonth()->startOfDay();
        $endDate   = $month->copy()->endOfMonth()->endOfDay();

        /*
    |--------------------------------------------------------------------------
    | 3️⃣ Fetch Attendance Records
    |--------------------------------------------------------------------------
    */

        $attendances = DB::table('attendances')
            ->select(
                'date',
                'actual_minutes',
                'status'
            )
            ->where('employee_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->toDateString();
            });

        /*
    |--------------------------------------------------------------------------
    | 4️⃣ Fetch Logs (Type Wise IN / OUT)
    |--------------------------------------------------------------------------
    */

        $logs = DB::table('attendance_logs')
            ->select(
                DB::raw("DATE(CONVERT_TZ(created_at,'+00:00','+05:30')) as date"),
                DB::raw("MIN(CASE WHEN punch_type = 'in' THEN created_at END) as check_in"),
                DB::raw("MAX(CASE WHEN punch_type = 'out' THEN created_at END) as check_out")
            )
            ->where('employee_id', $user->id)
            ->whereBetween(DB::raw("CONVERT_TZ(created_at,'+00:00','+05:30')"), [$startDate, $endDate])
            ->groupBy(DB::raw("DATE(CONVERT_TZ(created_at,'+00:00','+05:30'))"))
            ->get()
            ->keyBy('date');

        /*
    |--------------------------------------------------------------------------
    | 5️⃣ Prepare Monthly Summary
    |--------------------------------------------------------------------------
    */

        $present = 0;
        $absent = 0;
        $halfDay = 0;
        $overtimeDays = 0;

        $attendanceList = [];

        $currentDate = $month->copy()->startOfMonth();

        while ($currentDate <= $month->copy()->endOfMonth()) {

            if ($currentDate->isFuture()) {
                break;
            }

            $dateKey = $currentDate->toDateString();
            if ($currentDate->isSunday()) {

                $attendanceList[] = [
                    'date' => $dateKey,
                    'status' => 'holiday',
                    'punchIn' => null,
                    'punchOut' => null,
                    'hoursWorked' => "00:00"
                ];

                $currentDate->addDay();
                continue;
            }

            if (isset($attendances[$dateKey])) {

                $workedMinutes = $attendances[$dateKey]->actual_minutes ?? 0;
                $status = $attendances[$dateKey]->status ?? 'absent';

                $checkIn = isset($logs[$dateKey]->check_in)
                    ? Carbon::parse($logs[$dateKey]->check_in)->timezone('Asia/Kolkata')
                    : null;

                $checkOut = isset($logs[$dateKey]->check_out)
                    ? Carbon::parse($logs[$dateKey]->check_out)->timezone('Asia/Kolkata')
                    : null;

                $hoursWorked = gmdate("H:i", $workedMinutes * 60);

                // Count Summary
                switch ($status) {
                    case 'present':
                        $present++;
                        break;

                    case 'half_day':
                        $halfDay++;
                        break;

                    default:
                        $absent++;
                }

                // Get employee's expected shift for overtime check
                $empOfficeIn = $user->office_in_time ?: $this->getOfficeDefaults()['office_in'];
                $empOfficeOut = $user->office_out_time ?: $this->getOfficeDefaults()['office_out'];
                $empOfficeInParsed = Carbon::parse($dateKey . ' ' . $empOfficeIn);
                $empOfficeOutParsed = Carbon::parse($dateKey . ' ' . $empOfficeOut);
                $expectedShiftMinutes = $empOfficeInParsed->diffInMinutes($empOfficeOutParsed);

                // Overtime when worked > expected shift
                if ($workedMinutes > $expectedShiftMinutes) {
                    $overtimeDays++;
                }
            } else {

                $status = 'absent';
                $workedMinutes = 0;
                $checkIn = null;
                $checkOut = null;
                $hoursWorked = "00:00";

                $absent++;
            }

            $attendanceList[] = [
                'date' => $dateKey,
                'status' => $status,
                'punchIn' => $checkIn ? $checkIn->format('h:i A') : null,
                'punchOut' => $checkOut ? $checkOut->format('h:i A') : null,
                'hoursWorked' => $hoursWorked
            ];

            $currentDate->addDay();
        }

        /*
    |--------------------------------------------------------------------------
    | 6️⃣ Return Response
    |--------------------------------------------------------------------------
    */

        return response()->json([
            'status' => true,
            'month' => $month->format('Y-m'),
            'summary' => [
                'present' => $present,
                'absent' => $absent,
                'half_day' => $halfDay,
                'overtime_days' => $overtimeDays,
            ],
            'attendance' => $attendanceList
        ]);
    }



    public function markManualAttendance(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,half_day,paid_leave'
        ]);

        $date = Carbon::parse($request->date);

        // 🚫 Prevent future marking
        if ($date->isFuture()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot mark future attendance.'
            ], 422);
        }

        DB::beginTransaction();

        try {

            // 🎯 Get employee's shift to calculate minutes
            $employee = User::where('id', $request->employee_id)->first();
            $defaults = $this->getOfficeDefaults();

            $officeInTime = $employee->office_in_time ?? $defaults['office_in'];
            $officeOutTime = $employee->office_out_time ?? $defaults['office_out'];

            $officeIn = Carbon::parse($date->toDateString() . ' ' . $officeInTime);
            $officeOut = Carbon::parse($date->toDateString() . ' ' . $officeOutTime);
            $expectedShiftMinutes = $officeIn->diffInMinutes($officeOut);

            // Calculate minutes based on status
            $workingMinutes = 0;
            $actualMinutes = 0;

            if ($request->status === 'present') {
                $workingMinutes = $expectedShiftMinutes;     // Full shift
                $actualMinutes = $expectedShiftMinutes;
            }

            if ($request->status === 'half_day') {
                $workingMinutes = (int)($expectedShiftMinutes * 0.5); // 50% of shift
                $actualMinutes = (int)($expectedShiftMinutes * 0.5);
            }

            // ❌ Absent / Paid Leave = 0 minutes
            $dateStringForDb = $date->toDateString();

            DB::table('attendance_logs')
                ->where('employee_id', $request->employee_id)
                ->where('date', $dateStringForDb)
                ->delete();

            $attendance = Attendance::updateOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'date' => $date->toDateString(),
                ],
                [
                    'status' => $request->status,
                    'working_minutes' => $workingMinutes,
                    'actual_minutes' => $actualMinutes,
                    'overtime_minutes' => 0,
                    'created_at' => $date


                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully',
                'data' => $attendance
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance'
            ], 500);
        }
    }
}
