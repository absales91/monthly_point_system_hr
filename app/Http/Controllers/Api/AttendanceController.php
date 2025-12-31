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
        $today = Carbon::today()->toDateString();

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
        'latitude' => 'required',
        'longitude' => 'required',
        'image' => 'required|image|max:2048',
    ]);

    $employee = auth()->user(); // employee table
    $today = Carbon::today();

    $attendance = Attendance::where('employee_id', $employee->id)
        ->where('date', $today)
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

    // 🕒 Calculate working minutes
    $checkIn  = Carbon::parse($attendance->check_in);
    $checkOut = Carbon::now();
    $minutes  = $checkOut->diffInMinutes($checkIn);

    // 🟢 Status logic
    if ($minutes >= 480) {
        $status = 'present';
    } elseif ($minutes >= 240) {
        $status = 'half_day';
    } else {
        $status = 'absent';
    }

    // 📸 Save selfie
    $path = $request->file('image')->store('attendance', 'public');

    $attendance->update([
        'check_out' => $checkOut->format('H:i:s'),
        'working_minutes' => $minutes,
        'status' => $status,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Check-out successful',
        'working_minutes' => $minutes,
    ]);
}
}