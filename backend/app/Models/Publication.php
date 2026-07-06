<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    protected $fillable = [
        'publication_category_id',
        'uploaded_by_admin_id',
        'title',
        'slug',
        'description',
        'subject',
        'edition',
        'publication_year',
        'department',
        'price',
        'currency',
        'cover_image_path',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'status',
        'is_featured',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'publication_year' => 'integer',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(PublicationCategory::class, 'publication_category_id');
    }

    public function purchases()
    {
        return $this->hasMany(PublicationPurchase::class);
    }
}
