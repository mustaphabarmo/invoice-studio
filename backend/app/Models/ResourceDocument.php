<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceDocument extends Model
{
    protected $fillable = [
        'uploaded_by_admin_id',
        'title',
        'description',
        'category',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'visibility',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];
}
