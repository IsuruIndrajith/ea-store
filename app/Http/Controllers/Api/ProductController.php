<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::query()
                ->where('is_active', true)
                ->with(['categories', 'setReview'])
                ->orderBy('id')
                ->get()
        );
    }

    public function show(Request $request, string $slug): ProductResource
    {
        $preview = $request->query('preview_token') === env('INTERNAL_API_KEY');

        $product = Product::query()
            ->when(! $preview, fn ($q) => $q->where('is_active', true))
            ->where('slug', $slug)
            ->with(['categories', 'setReview'])
            ->firstOrFail();

        return new ProductResource($product);
    }
}
