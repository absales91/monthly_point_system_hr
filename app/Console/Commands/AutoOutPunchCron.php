<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoOutPunchCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-out';

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

        $employees = DB::table('users')
            ->where('role', 'employee')
            ->get();

        foreach ($employees as $employee) {

            $logs = DB::table('attendance_logs')
                ->where('employee_id', $employee->id)
                ->where('date', $today)
                ->orderBy('created_at')
                ->get();

            if ($logs->isEmpty()) {
                continue; // Absent handled by other cron
            }

            $lastPunch = $logs->last();

            // 🔴 If last punch is IN → auto OUT
            if ($lastPunch->punch_type === 'in') {

                $officeOut = Carbon::parse(
                    $today . ' ' . $employee->office_out_time
                );

                DB::table('attendance_logs')->insert([
                    'employee_id' => $employee->id,
                    'date'        => $today,
                    'punch_type'  => 'out',
                    'image'       => null, // auto out
                    'latitude'    => null,
                    'longitude'   => null,
                    'created_at'  => $officeOut,
                    'updated_at'  => $officeOut,
                ]);
            }
        }

        $this->info("Auto OUT punch completed for {$today}");
    }
}
