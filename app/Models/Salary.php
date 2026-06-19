<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'gross_salary',
        'deductions',
        'net_salary',
        'working_days',
        'present_days',
        'half_days',
        'absent_days',
        'overtime_minutes'
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
