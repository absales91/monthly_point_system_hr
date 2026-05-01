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
        $nowIst = Carbon::now('Asia/Kolkata');
        $today  = $nowIst->toDateString();

        // ❌ Skip Sunday (you can extend for holidays later)
        if ($nowIst->isSunday()) {
            $this->info("Sunday detected. Skipping.");
            return;
        }

        // ✅ Get IST day range in UTC
        [$startUtc, $endUtc] = $this->getIstDayRangeUtc();
        $nowUtc = Carbon::now();

        // ✅ Process in chunks (scalable)
        DB::table('users')
            ->where('role', 'employee')
            ->orderBy('id')
            ->chunk(100, function ($employees) use ($startUtc, $endUtc, $today,$nowUtc) {

                foreach ($employees as $employee) {

                    // ✅ Check if any punch exists today (UTC-safe)
                    $hasPunch = DB::table('attendance_logs')
                        ->where('employee_id', $employee->id)
                        ->whereBetween('created_at', [$startUtc, $endUtc])
                        ->exists();

                    if ($hasPunch) {
                        continue;
                    }

                    // ✅ Skip if already marked
                    $exists = DB::table('attendances')
                        ->where('employee_id', $employee->id)
                        ->where('date', $today)
                        ->exists();

                    if ($exists) {
                        continue;
                    }


                    // ✅ Mark absent
                    DB::table('attendances')->updateOrInsert(
                        [
                            'employee_id' => $employee->id,
                            'date'        => $today,
                        ],
                        [
                            'working_minutes'  => 0,
                            'actual_minutes'   => 0,
                            'overtime_minutes' => 0,
                            'status'           => 'absent',
                            'created_at'       => $nowUtc,
                            'updated_at'       => $nowUtc,
                        ]
                    );
                }
            });

        $this->info("Absent marking completed for {$today}");
    }

    /**
     * Convert IST day start/end to UTC range
     */
    private function getIstDayRangeUtc($date = null)
    {
        $ist = $date
            ? Carbon::parse($date, 'Asia/Kolkata')
            : Carbon::now('Asia/Kolkata');

        return [
            $ist->copy()->startOfDay()->timezone('UTC'),
            $ist->copy()->endOfDay()->timezone('UTC'),
        ];
    }
}
