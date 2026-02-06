<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceAdmin extends Component
{
    public $date;

    public function mount()
    {
        $this->date = now()->toDateString();
    }

    /** 
     * ✅ LIVEWIRE ACTION CALLED FROM BUTTON
     */
    public function markAttendance($employeeId, $status)
    {
        Attendance::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $this->date,
            ],
            [
                'status' => $status,
                'working_minutes' => 0,      // default (you can change later)
                'actual_minutes' => 0,
                'overtime_minutes' => 0,
                'updated_at' => now(),
            ]
        );

        $this->dispatch('attendance-updated'); // optional event
        session()->flash('success', "Attendance marked as {$status}");
    }
      public function markAbsent($employeeId, $date)
    {
        // 1️⃣ Delete any existing punch logs for that day
        DB::table('attendance_logs')
            ->where('employee_id', $employeeId)
            ->where('date', $date)
            ->delete();

        // 2️⃣ Update / create attendance record as ABSENT
        Attendance::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $date,
            ],
            [
                'status' => 'absent',
                'working_minutes' => 0,
                'actual_minutes' => 0,
                'overtime_minutes' => 0,
            ]
        );

        $this->dispatch('attendance-updated');
        session()->flash('success', "Marked ABSENT for {$date}");
    }
    public function markLeave($employeeId, $date)
    {
        // 1️⃣ Delete existing logs (leave has no IN/OUT)
        DB::table('attendance_logs')
            ->where('employee_id', $employeeId)
            ->where('date', $date)
            ->delete();

        // 2️⃣ Update attendance as LEAVE
        Attendance::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $date,
            ],
            [
                'status' => 'leave',
                'working_minutes' => 0,
                'actual_minutes' => 0,
                'overtime_minutes' => 0,
            ]
        );

        $this->dispatch('attendance-updated');
        session()->flash('success', "Marked LEAVE for {$date}");
    }

      public function render()
    {
        return view('livewire.attendance-admin', [
            'employees' => User::where('role', 'employee')->get(),
            'records' => Attendance::where('date', $this->date)
            ->whereIn('employee_id', User::where('role','employee')->pluck('id'))
            ->get()

        ]);
    }
    
}
