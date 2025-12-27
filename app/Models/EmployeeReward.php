<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeReward extends Model
{
     protected $fillable = [
        'employee_id',
        'reward_rule_id',
        'month',
        'year',
        'points_used',
        'reward_value',
        'status',
    ];
}
