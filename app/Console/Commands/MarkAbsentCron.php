<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkAbsentCron extends Command
{
    protected $signature = 'app:mark-absent-cron';

    protected $description = 'Mark absent for employees who did not punch today';

    public function handle()
    {
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        // 🔹 Get all active employees
        $employees = DB::table('users')
           ->where('role', 'employee')
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
