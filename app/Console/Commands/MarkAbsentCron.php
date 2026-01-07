<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkAbsentCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mark-absent-cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        // 🔹 Get all active employees
        $employees = DB::table('employees')
            ->where('status', 'active') // if you have status column
            ->get();

        foreach ($employees as $employee) {

            // ❌ Already has attendance?
            $exists = DB::table('attendances')
                ->where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->exists();

            if ($exists) {
                continue;
            }

            // ✅ INSERT ABSENT ENTRY
            DB::table('attendances')->insert([
                'employee_id' => $employee->id,
                'date' => $today,
                'check_in' => null,
                'check_out' => null,
                'working_minutes' => 0,
                'status' => 'absent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info("Absent marked for date: $today");
    }
}
