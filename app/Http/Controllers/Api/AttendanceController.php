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
     * Replaces checkIn & checkOut
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

        // 📝 Insert punch log
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

        // 🔄 Recalculate attendance on OUT
        if ($request->type === 'out') {
            $this->calculateTodayAttendance($employeeId);
        }

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

        // 🔹 User HR settings
        $user = DB::table('users')->where('id', $employeeId)->first();

        // 🔹 Approved leave check
        // $onLeave = DB::table('employee_leaves')
        //     ->where('employee_id', $employeeId)
        //     ->where('date', $today)
        //     ->where('status', 'approved')
        //     ->exists();

        // if ($onLeave) {
        //     Attendance::updateOrCreate(
        //         ['employee_id' => $employeeId, 'date' => $today],
        //         ['working_minutes' => 0, 'status' => 'leave']
        //     );
        //     return 0;
        // }

        // 🔹 Office timings
        $officeIn  = Carbon::parse($today . ' ' . $user->office_in_time);
        $officeOut = Carbon::parse($today . ' ' . $user->office_out_time);

        $fullDayMinutes = $officeIn->diffInMinutes($officeOut);
        $halfDayMinutes = ($user->half_day_hours ?? 4) * 60;

        // 🔹 Punch logs
        $logs = DB::table('attendance_logs')
            ->where('employee_id', $employeeId)
            ->where('date', $today)
            ->orderBy('created_at')
            ->get();

        if ($logs->isEmpty()) {
            Attendance::updateOrCreate(
                ['employee_id' => $employeeId, 'date' => $today],
                ['working_minutes' => 0, 'status' => 'absent']
            );
            return 0;
        }

        // 🔹 First IN punch (safe late check)
        $firstInLog = $logs->firstWhere('punch_type', 'in');

        if (!$firstInLog) {
            Attendance::updateOrCreate(
                ['employee_id' => $employeeId, 'date' => $today],
                ['working_minutes' => 0, 'status' => 'absent']
            );
            return 0;
        }

        // 🔁 Calculate working minutes (handles missing OUT)
        $totalMinutes = 0;

        for ($i = 0; $i < count($logs); $i++) {
            if ($logs[$i]->punch_type === 'in') {

                $in = Carbon::parse($logs[$i]->created_at);

                if (isset($logs[$i + 1]) && $logs[$i + 1]->punch_type === 'out') {
                    $out = Carbon::parse($logs[$i + 1]->created_at);
                    $i++; // skip next
                } else {
                    // Missing OUT → assume office out
                    $out = $officeOut;
                }

                if ($out->greaterThan($in)) {
                    $totalMinutes += $in->diffInMinutes($out);
                }
            }
        }

        // 🔹 Late calculation
        $firstIn = Carbon::parse($firstInLog->created_at);
        $lateGrace = $user->late_minutes_allowed ?? 15;
        $lateCutoff = $officeIn->copy()->addMinutes($lateGrace);
        $isLate = $firstIn->greaterThan($lateCutoff);

        // 🔹 Final status
        if ($totalMinutes >= $fullDayMinutes) {
            $status = $isLate ? 'late' : 'present';
        } elseif ($totalMinutes >= $halfDayMinutes) {
            $status = 'half_day';
        } else {
            $status = 'absent';
        }

        Attendance::updateOrCreate(
            ['employee_id' => $employeeId, 'date' => $today],
            [
                'working_minutes' => $totalMinutes,
                'status'          => $status,
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
            'total_working_minutes' => $records->sum('working_minutes'),
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'records' => $records,
        ]);
    }

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
                ];
            });

        return response()->json([
            'success' => true,
            'date' => $date,
            'total_punches' => $punches->count(),
            'punches' => $punches,
        ]);
    }
}
