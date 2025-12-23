<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointRule extends Model
{
    protected $fillable = [
        'category',
        'label',
        'max_points',
        'manager_only',
    ];
}
