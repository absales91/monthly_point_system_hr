<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkAbsentCron extends Command
{
    protected $signature = 'attendance:mark-absent';

    protected $description = 'Mark absent for employees who did not punch today';

    public function handle()
    {
        $now = Carbon::now('Asia/Kolkata');
        $today = $now->toDateString();
        $officeStart = Carbon::parse($today . ' 10:00:00');
        if ($now->isSunday()) {
            $this->info("Today is Sunday. Skipping absent marking.");
            return;
        }

        $employees = DB::table('users')
            ->where('role', 'employee')
            ->where(function ($q) use ($today, $officeStart) {
                $q->whereDate('created_at', '<', $today)
                    ->orWhere(function ($q2) use ($today, $officeStart) {
                        $q2->whereDate('created_at', '=', $today)
                            ->whereTime('created_at', '<', $officeStart);
                    });
            })
            ->get();


        foreach ($employees as $employee) {

            /**
             * STEP 1: Check if employee has ANY punch today
             */
            $hasPunch = DB::table('attendance_logs')
                ->where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->exists();

            if ($hasPunch) {
                continue; // present or half-day will be handled separately
            }

            /**
             * STEP 2: Check if attendance already marked
             */
            $attendanceExists = DB::table('attendances')
                ->where('employee_id', $employee->id)
                ->where('date', $today)
                ->exists();

            if ($attendanceExists) {
                continue;
            }

            /**
             * STEP 3: Mark ABSENT in attendances table
             */
            DB::table('attendances')->updateOrInsert([
                'employee_id'     => $employee->id,
                'date'            => $today,
                'working_minutes' => 0,
                'status'          => 'absent',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        $this->info("Absent marked successfully for date: {$today}");
    }
}
