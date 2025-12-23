<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyPoint extends Model
{
    protected $fillable = [
        'employee_id',
        'month',

        'attendance',
        'punctuality',
        'behaviour',
        'participation',
        'decision_making',
        'creativity',
        'training',
        'test',

        'total',
        'rating'
    ];

    protected $casts = [
        
        'attendance'        => 'integer',
        'punctuality'       => 'integer',
        'behaviour'         => 'integer',
        'participation'     => 'integer',
        'decision_making'   => 'integer',
        'creativity'        => 'integer',
        'training'          => 'integer',
        'test'              => 'integer',
        'total'             => 'integer',
    ];

    /* 🔗 Relationship */
    public function employee()
    {
        return $this->belongsTo(User::class);
    }

    /* 🧮 CENTRAL CALCULATION LOGIC */
    public static function calculate(array $data): array
    {
        $total =
            ($data['attendance'] ?? 0) +
            ($data['punctuality'] ?? 0) +
            ($data['behaviour'] ?? 0) +
            ($data['participation'] ?? 0) +
            ($data['decision_making'] ?? 0) +
            ($data['creativity'] ?? 0) +
            ($data['training'] ?? 0) +
            ($data['test'] ?? 0);

        $rating = match (true) {
            $total >= 90 => 'Excellent',
            $total >= 75 => 'Good',
            $total >= 60 => 'Average',
            default      => 'Poor',
        };

        return [$total, $rating];
    }
}
