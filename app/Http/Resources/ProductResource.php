<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'version' => $this->version,
            'platform' => $this->platform,
            'market' => $this->market,
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->pluck('slug')->values()),
            'price' => $this->price_cents / 100,
            'sale_price' => $this->sale_price_cents / 100,
            'growth' => (float) $this->growth,
            'drawdown' => (float) $this->drawdown,
            'rating' => (float) $this->rating,
            'reviews' => $this->reviews,
            'hue' => $this->hue,
            'description' => $this->description,
            'features' => $this->features ?? [],
            'is_purchasable' => $this->is_active
                && $this->price_cents > 0
                && $this->sale_price_cents > 0
                && $this->sale_price_cents <= $this->price_cents
                && $this->file_path !== null
                && Storage::disk('local')->exists($this->file_path),
        ];
    }
}
