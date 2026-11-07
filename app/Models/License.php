<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $guarded = [];
    protected $casts = ['expires_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function product() { return $this->belongsTo(Product::class); }

    public static function generateKey(): string
{
    return strtoupper(implode('-', str_split(bin2hex(random_bytes(10)), 5)));
}
}
