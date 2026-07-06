<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberWallet extends Model
{
    protected $fillable = [
        'member_id',
        'balance',
        'currency',
        'account_number',
        'account_name',
        'bank_name',
        'provider_payload',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'provider_payload' => 'array',
    ];

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
