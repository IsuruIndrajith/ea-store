<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SetReviewResource;
use App\Models\SetReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SetReviewController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = SetReview::query()->where('is_published', true);

        // Allow internal preview of unpublished reviews
        if ($request->header('X-Internal-Key') === config('services.ea_store.internal_key')) {
            $query = SetReview::query();
        }

        return SetReviewResource::collection($query->latest()->get());
    }

    public function show(Request $request, string $slug): SetReviewResource
    {
        $isInternal = $request->header('X-Internal-Key') === config('services.ea_store.internal_key');
        $isPreview  = $request->query('preview_token') === env('INTERNAL_API_KEY');

        $query = SetReview::where('slug', $slug);

        if (! $isInternal && ! $isPreview) {
            $query->where('is_published', true);
        }

        return new SetReviewResource($query->firstOrFail());
    }
}
