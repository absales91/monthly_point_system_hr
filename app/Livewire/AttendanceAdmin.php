<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

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
