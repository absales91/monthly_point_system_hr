<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AttendanceDayModal extends Component
{
    public $show = false;
    public $employeeId;
    public $date;
    public $status;
    public $start_time;
    public $end_time;
    public $staff;

    protected $listeners = [
        'open-attendance-modal' => 'openModal'
    ];

    public function openModal($employeeId, $date, $status)
    {
        $this->reset(['start_time', 'end_time']);

        $this->employeeId = $employeeId;
        $this->date = $date;
        $this->status = $status;
        // dd($this->employeeId);

        $this->staff = User::find($this->employeeId);

        $this->show = true;
    }

    public function saveAttendance()
    {
        // Ensure both times exist
        if (!$this->start_time || !$this->end_time) {
            session()->flash('error', 'Start time and End time required');
            return;
        }

        // Convert to Carbon objects
        $in = \Carbon\Carbon::parse($this->date . ' ' . $this->start_time);
        $out = \Carbon\Carbon::parse($this->date . ' ' . $this->end_time);

        // If end time is before start time, swap or error
        if ($out->lessThanOrEqualTo($in)) {
            session()->flash('error', 'End time must be after Start time');
            return;
        }
        DB::table('attendance_logs')
            ->where('employee_id', $this->employeeId)
            ->where('date', $this->date)
            ->delete();
        if ($this->start_time) {
            DB::table('attendance_logs')->insert([
                'employee_id' => $this->employeeId,
                'date'        => $this->date,
                'punch_type'  => 'in',
                'image'       => null,
                'latitude'    => null,
                'longitude'   => null,
                'created_at'  => $this->date . ' ' . $this->start_time,
                'updated_at'  => now(),
            ]);
        }

        // 2️⃣ Insert / update OUT punch log (if end time given)
        if ($this->end_time) {
            DB::table('attendance_logs')->insert([
                'employee_id' => $this->employeeId,
                'date'        => $this->date,
                'punch_type'  => 'out',
                'image'       => null,
                'latitude'    => null,
                'longitude'   => null,
                'created_at'  => $this->date . ' ' . $this->end_time,
                'updated_at'  => now(),
            ]);
        }

        // ✅ Calculate minutes
        $actualMinutes = $in->diffInMinutes($out);
        $in  = \Carbon\Carbon::parse($this->date . ' ' . $this->start_time);
        $out = \Carbon\Carbon::parse($this->date . ' ' . $this->end_time);

        if ($out->lessThanOrEqualTo($in)) {
            session()->flash('error', 'End time must be after Start time');
            return;
        }

        // ---- DYNAMIC OFFICE TIME ----
        $user = \App\Models\User::find($this->employeeId);

        $defaultOfficeIn  = '10:00:00';
        $defaultOfficeOut = '19:00:00';

        $officeInTime  = $user->office_in_time  ?? $defaultOfficeIn;
        $officeOutTime = $user->office_out_time ?? $defaultOfficeOut;

        $officeIn  = \Carbon\Carbon::parse($this->date . ' ' . $officeInTime);
        $officeOut = \Carbon\Carbon::parse($this->date . ' ' . $officeOutTime);
        // -----------------------------

        $fullDayMinutes = $officeIn->diffInMinutes($officeOut);

        $actualMinutes   = $in->diffInMinutes($out);
        $workingMinutes  = min($actualMinutes, $fullDayMinutes);
        $overtimeMinutes = max(0, $actualMinutes - $fullDayMinutes);


        // ✅ Update attendance table (NO check_in / check_out stored)
        \App\Models\Attendance::updateOrCreate(
            [
                'employee_id' => $this->employeeId,
                'date' => $this->date,
            ],
            [
                'status'           => $this->status,   // you choose
                'working_minutes'  => $workingMinutes,
                'actual_minutes'   => $actualMinutes,
                'overtime_minutes' => $overtimeMinutes,
            ]
        );

        $this->show = false;

        session()->flash('success', "Attendance saved for {$this->date}");
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
        return view('livewire.attendance-day-modal');
    }
}
