<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipRenewal extends Model
{
    protected $fillable = [
        'member_id',
        'membership_plan_id',
        'starts_at',
        'expires_at',
        'amount',
        'currency',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'expires_at' => 'date',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function plan()
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}
