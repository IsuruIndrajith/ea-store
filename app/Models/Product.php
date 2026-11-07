<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

        public function price(): string
        {
            return number_format($this->price_cents / 100, 2);
        }
}
