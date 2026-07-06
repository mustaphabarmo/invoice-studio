<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    protected $fillable = [
        'name',
        'grade',
        'amount',
        'currency',
        'duration_months',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'duration_months' => 'integer',
        'is_active' => 'boolean',
    ];

    public function renewals()
    {
        return $this->hasMany(MembershipRenewal::class);
    }
}
