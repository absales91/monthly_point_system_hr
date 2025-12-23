<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PointRule;

class PointRuleSeeder extends Seeder
{
    public function run()
    {
        $rules = [
            [
                'category' => 'attendance',
                'label' => 'Attendance',
                'max_points' => 30,
                'manager_only' => false,
            ],
            [
                'category' => 'punctuality',
                'label' => 'Punctuality',
                'max_points' => 15,
                'manager_only' => false,
            ],
            [
                'category' => 'behaviour',
                'label' => 'Behaviour',
                'max_points' => 15,
                'manager_only' => true,
            ],
            [
                'category' => 'discipline',
                'label' => 'Discipline',
                'max_points' => 10,
                'manager_only' => true,
            ],
            [
                'category' => 'participation',
                'label' => 'Participation',
                'max_points' => 15,
                'manager_only' => false,
            ],
            [
                'category' => 'decision_making',
                'label' => 'Decision Making',
                'max_points' => 15,
                'manager_only' => true,
            ],
            [
                'category' => 'creativity',
                'label' => 'Creativity',
                'max_points' => 5,
                'manager_only' => true,
            ],
            [
                'category' => 'training',
                'label' => 'Training',
                'max_points' => 5,
                'manager_only' => false,
            ],
            [
                'category' => 'test',
                'label' => 'Test / Assessment',
                'max_points' => 10,
                'manager_only' => false,
            ],
        ];

        foreach ($rules as $rule) {
            PointRule::updateOrCreate(
                ['category' => $rule['category']],
                $rule
            );
        }
    }
}
