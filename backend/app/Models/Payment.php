<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'member_id',
        'payable_type',
        'payable_id',
        'purpose',
        'provider',
        'reference',
        'provider_reference',
        'amount',
        'currency',
        'status',
        'checkout_url',
        'provider_payload',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'provider_payload' => 'array',
        'paid_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }
}
