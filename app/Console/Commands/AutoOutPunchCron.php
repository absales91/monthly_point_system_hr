<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoOutPunchCron extends Command
{
    protected $signature = 'attendance:auto-out';
    protected $description = 'Auto punch out employees who forgot to punch out';

    public function handle()
    {
        $now = Carbon::now('Asia/Kolkata');
        $today = $now->toDateString(); // 11:55 PM execution

        /*
        |--------------------------------------------------------------------------
        | Get employees whose last punch is IN today
        |--------------------------------------------------------------------------
        */

        $employees = DB::table('attendance_logs')
            ->select('employee_id')
            ->whereDate('created_at', $today)
            ->groupBy('employee_id')
            ->havingRaw("
                MAX(CASE WHEN punch_type = 'in' THEN created_at END) >
                MAX(CASE WHEN punch_type = 'out' THEN created_at END)
                OR MAX(CASE WHEN punch_type = 'out' THEN created_at END) IS NULL
            ")
            ->pluck('employee_id');

        foreach ($employees as $employeeId) {

            $employee = DB::table('users')
                ->where('id', $employeeId)
                ->where('role', 'employee')
                ->first();

            if (!$employee) continue;

            // Determine office out time
            $officeOutTime = $employee->office_out_time 
                ?? $this->getOfficeDefaults()['office_out'];

            $officeOut = Carbon::parse(
                $today . ' ' . $officeOutTime,
                'Asia/Kolkata'
            );

            DB::transaction(function () use ($employeeId, $today, $officeOut) {

                /*
                |--------------------------------------------------------------------------
                | 1️⃣ Get Last Punch
                |--------------------------------------------------------------------------
                */

                $lastPunch = DB::table('attendance_logs')
                    ->where('employee_id', $employeeId)
                    ->whereDate('created_at', $today)
                    ->orderByDesc('created_at')
                    ->first();

                if (!$lastPunch || $lastPunch->punch_type !== 'in') {
                    return;
                }

                $lastInTime = Carbon::parse($lastPunch->created_at);

                // SAFETY: Prevent OUT earlier than IN
                if ($officeOut->lessThanOrEqualTo($lastInTime)) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | 2️⃣ Insert Auto OUT
                |--------------------------------------------------------------------------
                */

                DB::table('attendance_logs')->insert([
                    'employee_id' => $employeeId,
                    'date'        => $today,
                    'punch_type'  => 'out',
                    'created_at'  => $officeOut,
                    'updated_at'  => $officeOut,
                ]);

                /*
                |--------------------------------------------------------------------------
                | 3️⃣ Fetch All Logs Again (Sorted)
                |--------------------------------------------------------------------------
                */

                $logs = DB::table('attendance_logs')
                    ->where('employee_id', $employeeId)
                    ->whereDate('created_at', $today)
                    ->orderBy('created_at')
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | 4️⃣ Safe Working Minutes Calculation
                |--------------------------------------------------------------------------
                */

                $workedMinutes = 0;
                $lastIn = null;

                foreach ($logs as $log) {

                    if ($log->punch_type === 'in') {

                        if (!$lastIn) {
                            $lastIn = Carbon::parse($log->created_at);
                        }

                    } elseif ($log->punch_type === 'out') {

                        if ($lastIn) {

                            $outTime = Carbon::parse($log->created_at);

                            // Extra safety: only calculate if OUT > IN
                            if ($outTime->greaterThan($lastIn)) {
                                $workedMinutes += $outTime->diffInMinutes($lastIn);
                            }

                            $lastIn = null;
                        }
                    }
                }

                // Final protection
                $workedMinutes = max(0, $workedMinutes);

                /*
                |--------------------------------------------------------------------------
                | 5️⃣ Determine Status
                |--------------------------------------------------------------------------
                */

                if ($workedMinutes >= 480) {
                    $status = 'present';
                } elseif ($workedMinutes >= 240) {
                    $status = 'half_day';
                } else {
                    $status = 'absent';
                }

                /*
                |--------------------------------------------------------------------------
                | 6️⃣ Update Attendance Table
                |--------------------------------------------------------------------------
                */

                DB::table('attendances')->updateOrInsert(
                    [
                        'employee_id' => $employeeId,
                        'date' => $today
                    ],
                    [
                        'actual_minutes' => $workedMinutes,
                        'working_minutes' => $workedMinutes,
                        'status' => $status,
                        'updated_at' => now()
                    ]
                );
            });
        }

        $this->info("Auto OUT + Attendance Update completed for {$today}");
    }

    /*
    |--------------------------------------------------------------------------
    | Default Office Timing
    |--------------------------------------------------------------------------
    */

    private function getOfficeDefaults()
    {
        return [
            'office_in' => '10:00:00',
            'office_out' => '19:00:00',
            'half_day_hours' => 4,
            'late_minutes_allowed' => 15,
        ];
    }
}
