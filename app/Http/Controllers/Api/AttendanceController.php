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

        // ❌ Prevent double IN or OUT
        if ($lastPunch && $lastPunch->punch_type === $request->type) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid punch sequence',
            ], 400);
        }

        // 📸 Store image
        $image = $request->file('image');
        $filename = $request->type . '_' . time() . '_' . $image->getClientOriginalName();
        $path = $image->storeAs('attendance', $filename, 'public');

        // 📝 Insert punch log
        DB::table('attendance_logs')->insert([
            'employee_id' => $employeeId,
            'date'        => $today,
            'punch_type'  => $request->type,
            'image'       => 'storage/' . $path,
            'latitude'    => $request->latitude,
            'longitude'   => $request->longitude,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        // 🔄 Recalculate attendance after OUT
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

        $logs = DB::table('attendance_logs')
            ->where('employee_id', $employeeId)
            ->where('date', $today)
            ->orderBy('created_at')
            ->get();

        $totalMinutes = 0;

        for ($i = 0; $i < count($logs); $i++) {
            if (
                $logs[$i]->punch_type === 'in' &&
                isset($logs[$i + 1]) &&
                $logs[$i + 1]->punch_type === 'out'
            ) {
                $in  = Carbon::parse($logs[$i]->created_at);
                $out = Carbon::parse($logs[$i + 1]->created_at);
                $totalMinutes += $in->diffInMinutes($out);
            }
        }

        // 🟢 Status logic
        if ($totalMinutes >= 480) {
            $status = 'present';
        } elseif ($totalMinutes >= 240) {
            $status = 'half_day';
        } else {
            $status = 'absent';
        }

        Attendance::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date'        => $today,
            ],
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
}
