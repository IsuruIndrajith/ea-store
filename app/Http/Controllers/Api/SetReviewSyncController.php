<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SetReviewResource;
use App\Models\Product;
use App\Models\SetReview;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SetReviewSyncController extends Controller
{
    public function __invoke(Request $request): SetReviewResource
    {
        $data = $request->validate([
            'product_slug'     => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255'],
            'h1'               => ['nullable', 'string', 'max:255'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'focus_keyword'    => ['nullable', 'string', 'max:255'],
            'thumbnail_url'    => ['nullable', 'string', 'max:500'],
            'is_published'     => ['boolean'],
            'content'          => ['nullable', 'array'],
        ]);

        $product = Product::where('slug', $data['product_slug'])->first();

        $slug = $data['slug'] ?? Str::slug($data['product_slug'] . '-settings-guide');

        $setReview = SetReview::updateOrCreate(
            ['product_slug' => $data['product_slug']],
            [
                'product_id'       => $product?->id,
                'slug'             => $slug,
                'h1'               => $data['h1'] ?? null,
                'meta_title'       => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'focus_keyword'    => $data['focus_keyword'] ?? null,
                'thumbnail_url'    => $data['thumbnail_url'] ?? null,
                'is_published'     => $data['is_published'] ?? false,
                'content'          => $data['content'] ?? null,
            ]
        );

        return new SetReviewResource($setReview);
    }
}
