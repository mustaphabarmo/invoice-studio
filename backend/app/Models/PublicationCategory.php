<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicationCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function publications()
    {
        return $this->hasMany(Publication::class);
    }
}
