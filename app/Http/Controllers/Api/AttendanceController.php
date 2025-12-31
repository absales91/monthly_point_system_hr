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
}