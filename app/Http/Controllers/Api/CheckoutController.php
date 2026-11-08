<?php

namespace App\Http\Controllers\Api;

use App\Contracts\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Stripe\Exception\ApiErrorException;

class CheckoutController extends Controller
{
    public function store(Request $request, PaymentGateway $payments): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.slug' => ['required', 'string', 'distinct', 'exists:products,slug'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $products = Product::query()
            ->whereIn('slug', collect($data['items'])->pluck('slug'))
            ->where('is_active', true)
            ->with('categories')
            ->get()
            ->keyBy('slug');

        $lines = collect($data['items'])->map(function (array $item) use ($products) {
            $product = $products->get($item['slug']);

            if (
                ! $product
                || $product->price_cents <= 0
                || $product->sale_price_cents <= 0
                || $product->sale_price_cents > $product->price_cents
                || ! $product->file_path
                || ! Storage::disk('local')->exists($product->file_path)
            ) {
                throw ValidationException::withMessages([
                    'items' => ["{$item['slug']} is not currently available for purchase."],
                ]);
            }

            return [
                'product' => $product,
                'quantity' => $item['qty'],
                'price_cents' => $product->sale_price_cents,
            ];
        });

        $order = DB::transaction(function () use ($request, $lines) {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'total_cents' => $lines->sum(fn (array $line) => $line['price_cents'] * $line['quantity']),
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'price_cents' => $line['price_cents'],
                    'quantity' => $line['quantity'],
                    'product_file_path' => $line['product']->file_path,
                ]);
            }

            return $order->load('items');
        });

        $frontendUrl = rtrim((string) config('services.frontend_url'), '/');

        try {
            $session = $payments->createCheckoutSession(
                $order,
                "{$frontendUrl}/account?checkout=success&session_id={CHECKOUT_SESSION_ID}",
                "{$frontendUrl}/cart?checkout=cancelled"
            );
        } catch (ApiErrorException|RuntimeException $exception) {
            $order->update(['status' => 'failed']);
            Log::error('Unable to create Stripe Checkout session.', [
                'order_id' => $order->id,
                'exception' => $exception,
            ]);

            return response()->json([
                'message' => 'Checkout could not be started. Please try again later.',
            ], 503);
        }

        $order->update(['stripe_session_id' => $session['id']]);

        return response()->json([
            'data' => ['order_id' => $order->id, 'checkout_url' => $session['url']],
        ], 201);
    }
}
