<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TrainingEvent extends Model
{
    protected $fillable = [
        'created_by_admin_id',
        'title',
        'slug',
        'tag',
        'event_type',
        'date_label',
        'starts_at',
        'time_label',
        'location',
        'venue',
        'description',
        'register_url',
        'image_paths',
        'status',
        'is_featured',
        'published_at',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'image_paths' => 'array',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'images',
        'image_url',
    ];

    public function getImagesAttribute(): array
    {
        return collect($this->image_paths ?? [])
            ->filter()
            ->map(fn (string $path) => url(Storage::disk('public')->url($path)))
            ->values()
            ->all();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->images[0] ?? null;
    }
}
