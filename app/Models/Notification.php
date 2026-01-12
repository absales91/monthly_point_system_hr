<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'employee_id',
        'title',
        'body',
        'type',
        'reference_id',
        'is_read'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
