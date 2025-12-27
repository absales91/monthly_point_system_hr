<?php

namespace App\Services;

use App\Models\EmployeeReward;
use App\Models\MonthlyPoint;
use App\Models\RewardRule;
use App\Models\RewardWallet;
use Illuminate\Support\Facades\DB;

class RewardEngineService
{
    public static function run(int $month, int $year): void
    {
        DB::transaction(function () use ($month, $year) {

            // ❌ Prevent duplicate generation for same month
            if (EmployeeReward::where('month', $month)
                ->where('year', $year)
                ->exists()) {
                throw new \Exception('Rewards already generated for this month.');
            }

            $rules = RewardRule::where('is_active', true)
                ->orderByDesc('point_threshold')
                ->get();

            $monthlyPoints = MonthlyPoint::where('month', $month)->get();

            foreach ($monthlyPoints as $mp) {

                // 🛑 Safety: skip employee if rewards already exist
                if (EmployeeReward::where('employee_id', $mp->employee_id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->exists()) {
                    continue;
                }

                $wallet = RewardWallet::firstOrCreate(
                    ['employee_id' => $mp->employee_id],
                    ['available_points' => 0, 'lifetime_points' => 0]
                );

                $availablePoints = $wallet->available_points + $mp->total;
                $wallet->lifetime_points += $mp->total;

                foreach ($rules as $rule) {

                    $count = intdiv($availablePoints, $rule->point_threshold);
                  
                    $count = min($count, $rule->max_per_month);

                    if ($count <= 0) continue;

                    for ($i = 0; $i < $count; $i++) {

                        EmployeeReward::create([
                            'employee_id'    => $mp->employee_id,
                            'reward_rule_id' => $rule->id,
                            'month'          => $month,
                            'year'           => $year,
                            'points_used'    => $rule->point_threshold,
                            'reward_value'   => $rule->reward_value,
                        ]);
                      

                        $availablePoints -= $rule->point_threshold;
                    }
                }

                $wallet->available_points = $availablePoints;
                $wallet->save();
            }
        });
    }
}
