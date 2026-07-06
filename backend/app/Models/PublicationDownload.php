<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicationDownload extends Model
{
    protected $fillable = [
        'member_id',
        'publication_id',
        'ip_address',
        'user_agent',
    ];

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }
}
