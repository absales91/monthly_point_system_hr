<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected   $fillable = [
        'title',
        'description',
        'assigned_by',
        'assigned_to',
        'priority',
        'due_date',
        'status',
    ];
    protected $casts = [
        'due_date' => 'date',
    ];

     // Employee who is assigned the task
    public function employee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Admin who assigned the task
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Employee to whom task is assigned
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    // Task updates / logs
    public function logs()
    {
        return $this->hasMany(TaskLog::class);
    }

    /* =======================
       Helpers (Optional)
    ======================= */

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isOverdue(): bool
    {
        return $this->due_date && now()->gt($this->due_date);
    }
}
