<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasUuids;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['user_id', 'invoice_number', 'client_name', 'status', 'data'];
    protected function casts(): array { return ['data' => 'array']; }
}
