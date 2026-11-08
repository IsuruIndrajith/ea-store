<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::query()
                ->where('is_active', true)
                ->with('categories')
                ->orderBy('id')
                ->get()
        );
    }

    public function show(string $slug): ProductResource
    {
        $product = Product::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->with('categories')
            ->firstOrFail();

        return new ProductResource($product);
    }
}
