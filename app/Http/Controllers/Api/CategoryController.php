<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Category::query()
                ->whereHas('products', fn ($query) => $query->where('is_active', true))
                ->orderBy('name')
                ->get(['slug', 'name']),
        ]);
    }
}
