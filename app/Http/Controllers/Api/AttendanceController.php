<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $employeeId = $request->user()->id; // users = employee
        $now = Carbon::now('Asia/Kolkata');
        $today = $now->toDateString();

        // ❌ Already checked-in today
        if (Attendance::where('employee_id', $employeeId)
            ->where('date', $today)
            ->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Already checked in today'
            ], 409);
        }

        // 📸 Store image
        $imagePath = $request->file('image')
            ->store('attendance', 'public');

        Attendance::create([
            'employee_id' => $employeeId,
            'date' => $today,
            'check_in' => Carbon::now()->format('H:i:s'),
            'check_in_image' => $imagePath,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 'present',
            'working_minutes' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful'
        ]);
    }

   public function checkOut(Request $request)
{
    $request->validate([
        'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        'latitude' => 'required',
        'longitude' => 'required',
    ]);

    $employee = $request->user();

    // ✅ Current IST time
    $now = Carbon::now('Asia/Kolkata');
    $today = $now->toDateString();

    $attendance = Attendance::where('employee_id', $employee->id)
        ->whereDate('date', $today)
        ->first();

    if (!$attendance || !$attendance->check_in) {
        return response()->json([
            'success' => false,
            'message' => 'Check-in not found',
        ], 400);
    }

    if ($attendance->check_out) {
        return response()->json([
            'success' => false,
            'message' => 'Already checked out',
        ], 400);
    }

    // 🕒 SAFE CHECK-IN TIME (IST)
    $checkIn = Carbon::parse(
        $attendance->date . ' ' . $attendance->check_in,
        'Asia/Kolkata'
    );

    // ❌ If somehow checkout before check-in
    if ($now->lessThan($checkIn)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid checkout time',
        ], 400);
    }

    // ✅ WORKING MINUTES
    $minutes = $checkIn->diffInMinutes($now);

    // 🟢 STATUS LOGIC
    if ($minutes >= 480) {
        $status = 'present';
    } elseif ($minutes >= 240) {
        $status = 'half_day';
    } else {
        $status = 'absent';
    }

    // 📸 STORE IMAGE
    $checkOutImage = $request->file('image')
        ->store('attendance', 'public');

    // ✅ UPDATE ATTENDANCE
    $attendance->update([
        'check_out' => $now->format('H:i:s'),
        'check_out_image' => $checkOutImage,
        'check_out_latitude' => $request->latitude,
        'check_out_longitude' => $request->longitude,
        'working_minutes' => $minutes,
        'status' => $status,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Check-out successful',
        'working_minutes' => $minutes,
        'check_in_time' => $checkIn->format('H:i:s'),
        'check_out_time' => $now->format('H:i:s'),
        'status' => $status,
        'timezone' => 'Asia/Kolkata',
    ]);
}



    public function attendanceSummary(Request $request)
    {
        $userId = $request->user()->id;

        // month format: YYYY-MM (2025-12)
        $month = $request->get('month', now()->format('Y-m'));

        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate   = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        // 📌 Attendance Records
        $records = DB::table('attendances')
            ->where('employee_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($row) {
                return [
                    'date' => Carbon::parse($row->date)->format('d M Y'),
                    'check_in' => $row->check_in ?? '--',
                    'check_out' => $row->check_out ?? '--',
                    'working_minutes' => (int) $row->working_minutes,
                    'status' => $row->status,
                ];
            });

        // 📊 Summary
        $summary = [
            'month' => $startDate->format('F Y'),
            'present' => $records->where('status', 'present')->count(),
            'half_day' => $records->where('status', 'half_day')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'leave' => $records->where('status', 'leave')->count(),
            'total_working_minutes' => $records->sum('working_minutes'),
        ];

        return response()->json([
            'summary' => $summary,
            'records' => $records,
        ]);
    }
}
