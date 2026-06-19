<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class EmployeeAttendanceTab extends Component
{
    public $employeeId;
    public $month;

    public function mount($employeeId)
    {
        $this->employeeId = $employeeId;
        $this->month = now()->format('Y-m'); // current month by default
    }

    public function render()
    {
        $startDate = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        $endDate   = Carbon::createFromFormat('Y-m', $this->month)->endOfMonth();

        $records = Attendance::where('employee_id', $this->employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
        

        return view('livewire.employee-attendance-tab', [
            'records' => $records,
            'employee' => User::find($this->employeeId),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'month' => $this->month,
        ]);
    }
}
