<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = config('services.stripe.webhook_secret');
        if (! is_string($secret) || $secret === '') {
            return response()->json(['message' => 'Stripe webhook is not configured.'], 503);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                $secret
            );
        } catch (UnexpectedValueException|SignatureVerificationException) {
            return response()->json(['message' => 'Invalid Stripe webhook signature or payload.'], 400);
        }

        $session = $event->data->object;
        if (in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            if (($session->payment_status ?? null) === 'paid') {
                $this->fulfill($session);
            }
        } elseif ($event->type === 'checkout.session.expired') {
            $this->setPendingOrderStatus($session, 'cancelled');
        } elseif ($event->type === 'checkout.session.async_payment_failed') {
            $this->setPendingOrderStatus($session, 'failed');
        }

        return response()->json(['received' => true]);
    }

    private function fulfill(object $session): void
    {
        $orderId = $session->metadata->order_id ?? $session->client_reference_id ?? null;
        if (! is_numeric($orderId)) {
            return;
        }

        DB::transaction(function () use ($session, $orderId) {
            $order = Order::query()
                ->whereKey((int) $orderId)
                ->where('stripe_session_id', $session->id ?? '')
                ->lockForUpdate()
                ->with('items')
                ->first();

            if (
                ! $order
                || $order->status !== 'pending'
                || ($session->payment_status ?? null) !== 'paid'
                || (int) ($session->amount_total ?? 0) !== $order->total_cents
                || strtolower((string) ($session->currency ?? '')) !== 'usd'
            ) {
                return;
            }

            $order->update(['status' => 'paid', 'paid_at' => now()]);

            foreach ($order->items as $item) {
                for ($unit = 0; $unit < $item->quantity; $unit++) {
                    License::create([
                        'user_id' => $order->user_id,
                        'product_id' => $item->product_id,
                        'order_item_id' => $item->id,
                        'key' => License::generateKey(),
                        'status' => 'active',
                        'max_activations' => 2,
                    ]);
                }
            }
        });
    }

    private function setPendingOrderStatus(object $session, string $status): void
    {
        $orderId = $session->metadata->order_id ?? $session->client_reference_id ?? null;
        if (! is_numeric($orderId)) {
            return;
        }

        Order::query()
            ->whereKey((int) $orderId)
            ->where('stripe_session_id', $session->id ?? '')
            ->where('status', 'pending')
            ->update(['status' => $status]);
    }
}
