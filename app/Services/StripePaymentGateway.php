<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use RuntimeException;
use Stripe\StripeClient;

class StripePaymentGateway implements PaymentGateway
{
    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): array
    {
        $secret = config('services.stripe.secret');

        if (! is_string($secret) || $secret === '') {
            throw new RuntimeException('Stripe is not configured.');
        }

        $session = (new StripeClient($secret))->checkout->sessions->create([
            'mode' => 'payment',
            'client_reference_id' => (string) $order->id,
            'metadata' => ['order_id' => (string) $order->id],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'line_items' => $order->items->map(fn ($item) => [
                'quantity' => $item->quantity,
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $item->price_cents,
                    'product_data' => ['name' => $item->product_name],
                ],
            ])->all(),
        ]);

        if (! is_string($session->url) || $session->url === '') {
            throw new RuntimeException('Stripe did not return a checkout URL.');
        }

        return ['id' => $session->id, 'url' => $session->url];
    }
}
