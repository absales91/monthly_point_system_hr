<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
            ->exists()) {
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

    // ✅ IST TIME
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

    // 🕒 CALCULATE WORKING MINUTES (SAFE)
    $checkIn = Carbon::createFromFormat(
        'Y-m-d H:i:s',
        $attendance->date . ' ' . $attendance->check_in,
        'Asia/Kolkata'
    );

    $minutes = abs($now->diffInMinutes($checkIn));

    // 🟢 STATUS LOGIC
    if ($minutes >= 480) {
        $status = 'present';
    } elseif ($minutes >= 240) {
        $status = 'half_day';
    } else {
        $status = 'absent';
    }

    // 📸 STORE CHECK-OUT IMAGE
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
        'check_out_time' => $now->format('H:i:s'),
        'timezone' => 'Asia/Kolkata',
    ]);
}


}