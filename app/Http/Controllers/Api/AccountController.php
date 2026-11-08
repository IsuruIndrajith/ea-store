<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function orders(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.licenses'])
            ->latest()
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'status' => $order->status,
                'total' => $order->total_cents / 100,
                'created_at' => $order->created_at,
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'licenses' => $item->licenses->map(fn ($license) => [
                        'id' => $license->id,
                        'key' => $license->key,
                        'status' => $license->status,
                    ])->values(),
                ])->values(),
            ]);

        return response()->json(['data' => $orders]);
    }
}
