<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active'       => 'boolean',
            'growth'          => 'float',
            'drawdown'        => 'float',
            'rating'          => 'float',
            'features'        => 'array',
            'extended'        => 'array',
            'set_review_data' => 'array',
        ];
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function setReview()
    {
        return $this->hasOne(SetReview::class, 'product_slug', 'slug');
    }
}
