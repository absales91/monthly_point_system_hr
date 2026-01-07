<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;

    protected $table = 'daily_reports';

    protected $fillable = [
        'employee_id',
        'date',
        'work_summary',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Relationship: Daily report belongs to an employee (user)
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
