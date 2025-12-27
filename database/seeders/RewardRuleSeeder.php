<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RewardRule;

class RewardRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // RewardRule::truncate(); // optional (clears old data)

        $rules = [
            [
                'reward_type'     => 'paid_leave',
                'reward_name'     => '1 Paid Leave',
                'point_threshold' => 1000,
                'reward_value'    => 1,
                'max_per_month'   => 1,
                'carry_forward'   => true,
                'is_active'       => true,
            ],
            [
                'reward_type'     => 'paid_leave',
                'reward_name'     => '2 Paid Leaves',
                'point_threshold' => 2000,
                'reward_value'    => 2,
                'max_per_month'   => 1,
                'carry_forward'   => true,
                'is_active'       => true,
            ],
            [
                'reward_type'     => 'cash',
                'reward_name'     => '₹1000 Cash Bonus',
                'point_threshold' => 3000,
                'reward_value'    => 1000,
                'max_per_month'   => 1,
                'carry_forward'   => false,
                'is_active'       => true,
            ],
            [
                'reward_type'     => 'badge',
                'reward_name'     => 'Star Performer Badge',
                'point_threshold' => 500,
                'reward_value'    => 1,
                'max_per_month'   => 1,
                'carry_forward'   => false,
                'is_active'       => true,
            ],
        ];

        foreach ($rules as $rule) {
            RewardRule::create($rule);
        }
    }
}
