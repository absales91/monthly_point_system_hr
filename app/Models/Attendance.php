<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
   protected $fillable = [
    'employee_id',
    'date',
    'check_in',
    'check_out',
    'check_in_image',
    'check_out_image',
    'latitude',
    'longitude',
    'check_out_latitude',
    'check_out_longitude',
    'working_minutes',
    'status',
];



    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
