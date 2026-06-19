<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
     use HasFactory;

    protected $fillable = [
        'task_id',
        'employee_id',
        'note',
        'image',
    ];

    /* =======================
       Relationships
    ======================= */

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class);
    }
}
