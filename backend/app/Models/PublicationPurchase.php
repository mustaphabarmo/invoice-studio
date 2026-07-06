<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicationPurchase extends Model
{
    protected $fillable = [
        'member_id',
        'publication_id',
        'amount',
        'currency',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }

    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}
