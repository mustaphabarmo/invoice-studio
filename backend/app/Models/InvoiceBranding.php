<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceBranding extends Model
{
    protected $fillable = ['user_id', 'data'];
    protected function casts(): array { return ['data' => 'array']; }
}
