<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;

class MarkAttendanceNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to employees who have not marked attendance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();

        // Employees who have NOT marked attendance today
        $employees = User::
    where('role', 'employee')
    ->whereDoesntHave('attendances', function ($q) use ($today) {
        $q->whereDate('date', $today);
    })
    ->get();


        if ($employees->isEmpty()) {
            $this->info('✅ All employees have marked attendance.');
            return;
        }

        foreach ($employees as $employee) {
            NotificationService::send(
                $employee,
                'Attendance Reminder ⏰',
                'Please mark your attendance for today.',
                'attendance_reminder',
                null
            );
        }

        $this->info('🔔 Attendance reminder sent to ' . $employees->count() . ' employees.');
    }
}
