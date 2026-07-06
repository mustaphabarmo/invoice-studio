<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'member_wallet_id',
        'member_id',
        'payment_id',
        'type',
        'purpose',
        'reference',
        'amount',
        'balance_before',
        'balance_after',
        'currency',
        'status',
        'description',
        'metadata',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
        'completed_at' => 'datetime',
    ];

    public function wallet()
    {
        return $this->belongsTo(MemberWallet::class, 'member_wallet_id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
