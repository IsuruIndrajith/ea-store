<?php

namespace App\Contracts;

use App\Models\Order;

interface PaymentGateway
{
    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): array;
}
