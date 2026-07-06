<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'membership_number',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'role',
        'status',
        'membership_grade',
        'organization',
        'job_title',
        'address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function renewals()
    {
        return $this->hasMany(MembershipRenewal::class, 'member_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'member_id');
    }

    public function wallet()
    {
        return $this->hasOne(MemberWallet::class, 'member_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'member_id');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'member_id');
    }

    public function publicationPurchases()
    {
        return $this->hasMany(PublicationPurchase::class, 'member_id');
    }

    public function downloads()
    {
        return $this->hasMany(PublicationDownload::class, 'member_id');
    }
}
