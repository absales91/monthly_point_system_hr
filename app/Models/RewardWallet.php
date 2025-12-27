<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardWallet extends Model
{
     protected $fillable = [
        'employee_id',
        'available_points',
        'lifetime_points',
    ];
}
