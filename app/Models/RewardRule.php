<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardRule extends Model
{
     protected $fillable = [
        'reward_type',
        'reward_name',
        'point_threshold',
        'reward_value',
        'max_per_month',
        'carry_forward',
        'is_active',
    ];
}
