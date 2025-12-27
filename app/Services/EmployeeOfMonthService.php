<?php

namespace App\Services;

use App\Models\MonthlyPoint;
use App\Models\EmployeeOfMonth;
use Illuminate\Support\Collection;

class EmployeeOfMonthService
{
    /**
     * Generate TWO employees of the month
     */
    public static function generate(string $month): Collection
    {
        // ❌ Prevent duplicate announcement for same month
        if (EmployeeOfMonth::where('month', $month)->exists()) {
            throw new \Exception('Employee of the month already announced.');
        }

        // 🔍 Pick top 2 employees
      $winner = MonthlyPoint::where('month', $month)
    ->whereHas('employee', fn ($q) => $q->where('role', 'employee'))
    ->orderByDesc('total')
    ->orderByDesc('attendance')
    ->orderBy('created_at')
    ->first();

if (!$winner) {
    throw new \Exception('Not enough employees to announce.');
}

EmployeeOfMonth::create([
    'month'       => $month,
    'employee_id' => $winner->employee_id,
    'points'      => $winner->total,
]);

return collect([$winner]); // ✅ return both winners
    }
}
