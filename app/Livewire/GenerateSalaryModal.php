<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Salary;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateSalaryModal extends Component
{
    public $showModal = false;
    public $employeeId;
    public $year;
    public $month;
    public $slipType = 'full'; // half or full

    protected $rules = [
        'year' => 'required',
        'month' => 'required',
        'slipType' => 'required|in:half,full',
    ];

    public function mount($employeeId)
    {
        $this->employeeId = $employeeId;
    }

      public function generate()
    {
        $this->validate();

        $staff = User::findOrFail($this->employeeId);

        $start = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $end   = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        // ---- Attendance Stats (like your PDF) ----
        $records = Attendance::where('employee_id', $this->employeeId)
            ->whereBetween('date', [$start, $end])
            ->get();

        $presentDays = $records->where('status', 'present')->count();
        $absentDays  = $records->where('status', 'absent')->count();
        $halfDays    = $records->where('status', 'half_day')->count();
        $weeklyOff   = 4; // example

        // ---- Salary Calculation (basic example) ----
        $monthlySalary = $staff->salary ?? 13000;

        $perDay = $monthlySalary / $end->daysInMonth;
        $earned = $presentDays * $perDay;

        $data = [
            'employee' => $staff,
            'start' => $start,
            'end' => $end,
            'present' => $presentDays,
            'absent' => $absentDays,
            'halfDays' => $halfDays,
            'weeklyOff' => $weeklyOff,
            'gross' => $monthlySalary,
            'earned' => round($earned, 2),
            'net' => round($earned, 2),
        ];

        // ---- Generate PDF ----
        $pdf = Pdf::loadView('salary.pdf', $data);

        // Optional: store salary
        Salary::updateOrCreate(
            [
                'employee_id' => $this->employeeId,
                'month' => "{$this->year}-{$this->month}",
            ],
            [
                'gross_salary' => $monthlySalary,
                'net_salary' => round($earned, 2),
                'pdf_path' => null,
            ]
        );

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "salary-slip-{$staff->name}-{$this->month}-{$this->year}.pdf"
        );
    }

    public function render()
    {
        return view('livewire.generate-salary-modal', [
            'years' => range(now()->year - 3, now()->year),
            'months' => range(1, 12),
        ]);
    }
}
