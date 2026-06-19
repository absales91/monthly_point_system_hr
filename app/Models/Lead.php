<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'unique_query_id',
        'query_type',
        'query_time',

        'name',
        'mobile',
        'email',
        'company',
        'address',
        'city',
        'state',
        'pincode',
        'country_iso',

        'subject',
        'product',
        'mcat',
        'message',

        'call_duration',
        'receiver_mobile',

        'source',
        'lead_status',
        'assigned_to',
        'last_follow_up_at',
        'next_follow_up_at',
    ];

    protected $casts = [
        'query_time'          => 'datetime',
        'last_follow_up_at'   => 'datetime',
        'next_follow_up_at'   => 'datetime',
    ];

    /* ================= RELATIONS ================= */

    // Sales person relation (optional)
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /* ================= HELPERS ================= */

    public function isHotLead(): bool
    {
        return in_array($this->query_type, ['B', 'W']);
    }

    public function isCallLead(): bool
    {
        return $this->query_type === 'P';
    }

    public function isInternational(): bool
    {
        return $this->country_iso && $this->country_iso !== 'IN';
    }
}
