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

        $timezone = 'Asia/Kolkata';
        $now = Carbon::now($timezone);
        $today = $now->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Get employees whose LAST punch is IN
        |--------------------------------------------------------------------------
        */

        $employees = DB::table('attendance_logs as a1')
            ->select('a1.employee_id')
            ->whereDate(DB::raw("CONVERT_TZ(a1.created_at,'+00:00','+05:30')"), $today)
            ->whereRaw('a1.id = (
                SELECT id FROM attendance_logs
                WHERE employee_id = a1.employee_id
                ORDER BY created_at DESC
                LIMIT 1
            )')
            ->where('a1.punch_type', 'in')
            ->pluck('employee_id');

        foreach ($employees as $employeeId) {

            $employee = DB::table('users')
                ->where('id', $employeeId)
                ->where('role', 'employee')
                ->first();

            if (!$employee) {
                continue;
            }

            $defaults = $this->getOfficeDefaults();

            $officeInTime  = $employee->office_in_time  ?: $defaults['office_in'];
            $officeOutTime = $employee->office_out_time ?: $defaults['office_out'];
            $lateAllowed   = $employee->late_minutes_allowed ?? $defaults['late_minutes_allowed'];
            $halfDayHours  = $employee->half_day_hours ?? $defaults['half_day_hours'];

            DB::transaction(function () use (
                $employeeId,
                $today,
                $now,
                $timezone,
                $officeInTime,
                $officeOutTime,
                $lateAllowed,
                $halfDayHours
            ) {

                /*
                |--------------------------------------------------------------------------
                | Fetch today's logs
                |--------------------------------------------------------------------------
                */

                $logs = DB::table('attendance_logs')
                    ->where('employee_id', $employeeId)
                    ->whereDate(DB::raw("CONVERT_TZ(created_at,'+00:00','+05:30')"), $today)
                    ->orderBy('created_at')
                    ->get();

                if ($logs->isEmpty()) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Auto OUT Punch
                |--------------------------------------------------------------------------
                */

                $lastLog = $logs->last();

                // Check if auto-out punch already exists to prevent duplicates
                $autoOutExists = DB::table('attendance_logs')
                    ->where('employee_id', $employeeId)
                    ->whereDate(DB::raw("CONVERT_TZ(created_at,'+00:00','+05:30')"), $today)
                    ->where('punch_type', 'out')
                    ->exists();

                if ($lastLog->punch_type === 'in' && !$autoOutExists) {

                    // Use employee's scheduled office_out_time for auto-punch
                    $outTime = Carbon::parse($today . ' ' . $officeOutTime, $timezone);

                    DB::table('attendance_logs')->insert([
                        'employee_id' => $employeeId,
                        'date'        => $today,
                        'punch_type'  => 'out',
                        'created_at'  => $outTime->utc(),
                        'updated_at'  => $outTime->utc(),
                    ]);

                    $logs = DB::table('attendance_logs')
                        ->where('employee_id', $employeeId)
                        ->whereDate(DB::raw("CONVERT_TZ(created_at,'+00:00','+05:30')"), $today)
                        ->orderBy('created_at')
                        ->get();
                }

                /*
                |--------------------------------------------------------------------------
                | Calculate Working Minutes
                |--------------------------------------------------------------------------
                */

                $workedMinutes = 0;
                $lastIn = null;

                foreach ($logs as $log) {

                    $logTime = Carbon::parse($log->created_at)->timezone($timezone);

                    if ($log->punch_type === 'in') {

                        if (!$lastIn) {
                            $lastIn = $logTime;
                        }

                    } elseif ($log->punch_type === 'out') {

                        if ($lastIn && $logTime->greaterThan($lastIn)) {
                            $workedMinutes += $logTime->diffInMinutes($lastIn);
                            $lastIn = null;
                        }
                    }
                }

                if ($lastIn) {
                    // Punch out was inserted at $now, so calculate to $now
                    $lastOut = Carbon::parse($logs->last()->created_at)->timezone($timezone);
                    $workedMinutes += $lastOut->diffInMinutes($lastIn);
                }

                $workedMinutes = max(0, $workedMinutes);

                /*
                |--------------------------------------------------------------------------
                | Late Detection
                |--------------------------------------------------------------------------
                */

                $officeIn = Carbon::parse($today . ' ' . $officeInTime, $timezone);

                $firstInLog = $logs->firstWhere('punch_type', 'in');

                $isLate = false;
                $lateMinutes = 0;

                if ($firstInLog) {

                    $firstInTime = Carbon::parse($firstInLog->created_at)->timezone($timezone);

                    if ($firstInTime->greaterThan($officeIn->copy()->addMinutes($lateAllowed))) {

                        $isLate = true;
                        $lateMinutes = $firstInTime->diffInMinutes($officeIn);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Attendance Status
                |--------------------------------------------------------------------------
                */

                // Calculate expected shift duration from office in/out times
                $officeInParsed = Carbon::parse($today . ' ' . $officeInTime, $timezone);
                $officeOutParsed = Carbon::parse($today . ' ' . $officeOutTime, $timezone);
                $expectedShiftMinutes = $officeOutParsed->diffInMinutes($officeInParsed);

                // Mark as present if worked >= 90% of expected shift
                $fullDayThreshold = $expectedShiftMinutes * 0.9;
                
                // Mark as half-day if worked >= 50% of expected shift (or half_day_hours, whichever is lower)
                $halfDayThreshold = min($expectedShiftMinutes * 0.5, $halfDayHours * 60);

                if ($workedMinutes >= $fullDayThreshold) {

                    $status = 'present';

                } elseif ($workedMinutes >= $halfDayThreshold) {

                    $status = 'half_day';

                } else {

                    $status = 'absent';
                }

                /*
                |--------------------------------------------------------------------------
                | Save Attendance
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
                        'is_late' => $isLate ? 1 : 0,
                        'late_minutes' => $status == 'absent' ? 0 : $lateMinutes,
                        'updated_at' => now()
                    ]
                );
            });
        }

        $this->info("Auto OUT completed for {$today}");
    }

    /*
    |--------------------------------------------------------------------------
    | Default Office Settings
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
