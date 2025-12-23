<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EmployeeOfMonth extends Model
{
    protected $fillable = [
        'month',
        'employee_id',
        'points',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class);
    }
}
