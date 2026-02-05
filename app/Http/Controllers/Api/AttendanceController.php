<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
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

            // Recalculate inside SAME transaction
            $this->calculateTodayAttendance($employeeId);
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
    private function calculateTodayAttendance($employeeId)
    {
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        $user = DB::table('users')->where('id', $employeeId)->first();
        if (!$user) {
            throw new \Exception("User not found for attendance calculation.");
        }

        $defaults = $this->getOfficeDefaults();

        $officeInTime  = $user->office_in_time ?? $defaults['office_in'];
        $officeOutTime = $user->office_out_time ?? $defaults['office_out'];
        $halfDayHours  = $user->half_day_hours ?? $defaults['half_day_hours'];
        $lateGrace     = $user->late_minutes_allowed ?? $defaults['late_minutes_allowed'];

        $officeIn  = Carbon::parse($today . ' ' . $officeInTime);
        $officeOut = Carbon::parse($today . ' ' . $officeOutTime);

        $fullDayMinutes = $officeIn->diffInMinutes($officeOut);
        $halfDayMinutes = $halfDayHours * 60;

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
            ->get();

        $summary = [
            'month' => $startDate->format('F Y'),
            'present' => $records->where('status', 'present')->count(),
            'half_day' => $records->where('status', 'half_day')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'total_working_minutes' => $records->sum('working_minutes'),
            'total_overtime_minutes' => $records->sum('overtime_minutes'),
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'records' => $records,
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
}
