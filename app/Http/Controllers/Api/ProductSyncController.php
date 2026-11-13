<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductSyncController extends Controller
{
    public function __invoke(Request $request): ProductResource
    {
        $data = $request->validate([
            'slug'              => ['required', 'string', 'max:255'],
            'name'              => ['required', 'string', 'max:255'],
            'version'           => ['required', 'string', 'max:50'],
            'platform'          => ['required', 'string', 'max:50'],
            'market'            => ['required', 'string', 'max:100'],
            'categories'        => ['array'],
            'categories.*'      => ['string'],
            'price_cents'       => ['required', 'integer', 'min:0'],
            'sale_price_cents'  => ['required', 'integer', 'min:0'],
            'hue'               => ['integer', 'min:0', 'max:360'],
            'growth'            => ['numeric'],
            'drawdown'          => ['numeric'],
            'description'       => ['nullable', 'string'],
            'meta_title'        => ['nullable', 'string', 'max:255'],
            'meta_description'  => ['nullable', 'string'],
            'short_description' => ['nullable', 'string'],
            'features'          => ['array'],
            'features.*'        => ['string'],
            'vendor_url'        => ['nullable', 'string', 'max:500'],
            'myfxbook_link'     => ['nullable', 'string', 'max:500'],
            'is_active'         => ['boolean'],
            'extended'          => ['nullable', 'array'],
            'set_review_data'   => ['nullable', 'array'],
        ]);

        $product = Product::updateOrCreate(
            ['slug' => $data['slug']],
            [
                'name'              => $data['name'],
                'version'           => $data['version'],
                'platform'          => $data['platform'],
                'market'            => $data['market'],
                'price_cents'       => $data['price_cents'],
                'sale_price_cents'  => $data['sale_price_cents'],
                'hue'               => $data['hue'] ?? 0,
                'growth'            => $data['growth'] ?? 0,
                'drawdown'          => $data['drawdown'] ?? 0,
                'description'       => $data['description'] ?? null,
                'meta_title'        => $data['meta_title'] ?? null,
                'meta_description'  => $data['meta_description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'features'          => $data['features'] ?? [],
                'vendor_url'        => $data['vendor_url'] ?? null,
                'myfxbook_link'     => $data['myfxbook_link'] ?? null,
                'is_active'         => $data['is_active'] ?? true,
                'extended'          => $data['extended'] ?? null,
                'set_review_data'   => $data['set_review_data'] ?? null,
            ]
        );

        if (! empty($data['categories'])) {
            $categoryIds = collect($data['categories'])->map(function (string $slug) {
                return Category::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => ucwords(str_replace(['-', '_'], ' ', $slug))]
                )->id;
            });

            $product->categories()->sync($categoryIds);
        }

        return new ProductResource($product->load('categories'));
    }
}
