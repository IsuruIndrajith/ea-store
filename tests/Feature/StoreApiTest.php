<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_endpoints_return_the_storefront_product_shape(): void
    {
        $this->seed(ProductSeeder::class);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(8, 'data')
            ->assertJsonPath('data.0.slug', 'gold-titan-mt5')
            ->assertJsonPath('data.0.price', 849)
            ->assertJsonPath('data.0.sale_price', 49.99)
            ->assertJsonPath('data.0.is_purchasable', false);

        $this->getJson('/api/products/gold-titan-mt5')
            ->assertOk()
            ->assertJsonPath('data.categories', ['gold-ea', 'forex-ea', 'low-drawdown']);

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'gold-ea', 'name' => 'Gold EA']);
    }

    public function test_checkout_uses_server_prices_and_payment_webhook_issues_one_license_per_unit(): void
    {
        Storage::disk('local')->put('ea-files/ready.ex5', 'signed product content');
        $product = $this->createPurchasableProduct();

        $token = $this->registerAndGetToken();

        $this->mock(PaymentGateway::class)
            ->shouldReceive('createCheckoutSession')
            ->once()
            ->andReturn(['id' => 'cs_test_storefront', 'url' => 'https://checkout.stripe.test/session']);

        $checkout = $this->withToken($token)->postJson('/api/checkout', [
            'items' => [['slug' => $product->slug, 'qty' => 2]],
        ])->assertCreated()
            ->assertJsonPath('data.checkout_url', 'https://checkout.stripe.test/session');

        $order = Order::with('items')->firstOrFail();
        $this->assertSame(9998, $order->total_cents);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertSame(4999, $order->items->first()->price_cents);
        $this->assertSame('ea-files/ready.ex5', $order->items->first()->product_file_path);

        config(['services.stripe.webhook_secret' => 'whsec_store_test']);
        $payload = json_encode([
            'id' => 'evt_checkout_paid',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_test_storefront',
                'object' => 'checkout.session',
                'metadata' => ['order_id' => (string) $order->id],
                'client_reference_id' => (string) $order->id,
                'payment_status' => 'paid',
                'amount_total' => 9998,
                'currency' => 'usd',
            ]],
        ], JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", 'whsec_store_test');
        $headers = ['HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}"];

        $this->call('POST', '/api/stripe/webhook', [], [], [], $headers, $payload)->assertOk();
        $this->call('POST', '/api/stripe/webhook', [], [], [], $headers, $payload)->assertOk();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid', 'total_cents' => 9998]);
        $this->assertSame(2, License::where('order_item_id', $order->items->first()->id)->count());

        $this->withToken($token)->getJson('/api/me/orders')
            ->assertOk()
            ->assertJsonPath('data.0.items.0.quantity', 2)
            ->assertJsonCount(2, 'data.0.items.0.licenses');

        $this->assertSame($checkout->json('data.order_id'), $order->id);
    }

    public function test_checkout_requires_authentication_and_rejects_products_without_delivery_files(): void
    {
        $product = Product::create([
            'name' => 'Not ready',
            'slug' => 'not-ready',
            'platform' => 'MT5',
            'market' => 'XAUUSD',
            'description' => 'Not deliverable yet.',
            'price_cents' => 10000,
            'sale_price_cents' => 5000,
            'file_path' => null,
            'is_active' => true,
        ]);

        $this->postJson('/api/checkout', ['items' => [['slug' => $product->slug, 'qty' => 1]]])
            ->assertUnauthorized();

        $token = $this->registerAndGetToken();
        $this->withToken($token)->postJson('/api/checkout', [
            'items' => [['slug' => $product->slug, 'qty' => 1]],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('items');
    }

    public function test_webhook_rejects_invalid_signatures(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_store_test']);

        $this->call('POST', '/api/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't=1,v1=invalid',
        ], '{"id":"evt_invalid","type":"checkout.session.completed","data":{"object":{}}}')
            ->assertBadRequest();
    }

    public function test_local_vite_origins_are_allowed_for_api_preflight_requests(): void
    {
        $this->call('OPTIONS', '/api/auth/register', [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost:8080',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'content-type,accept',
        ])->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:8080');
    }

    public function test_the_purchasing_user_can_download_a_paid_license(): void
    {
        Storage::disk('local')->put('ea-files/ready.ex5', 'signed product content');
        $product = $this->createPurchasableProduct();
        $owner = User::factory()->create();
        $license = $this->createPaidLicense($owner, $product);
        $ownerToken = $owner->createToken('test')->plainTextToken;

        $this->withToken($ownerToken)->get("/api/me/licenses/{$license->id}/download")->assertOk();
    }

    public function test_other_users_cannot_download_a_paid_license(): void
    {
        Storage::disk('local')->put('ea-files/ready.ex5', 'signed product content');
        $product = $this->createPurchasableProduct();
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $license = $this->createPaidLicense($owner, $product);
        $otherToken = $otherUser->createToken('test')->plainTextToken;

        $this->withToken($otherToken)->get("/api/me/licenses/{$license->id}/download")->assertNotFound();
    }

    private function createPaidLicense(User $owner, Product $product): License
    {
        $order = Order::create([
            'user_id' => $owner->id,
            'status' => 'paid',
            'total_cents' => 4999,
            'paid_at' => now(),
        ]);
        $item = $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price_cents' => 4999,
            'quantity' => 1,
            'product_file_path' => 'ea-files/ready.ex5',
        ]);
        $license = License::create([
            'user_id' => $owner->id,
            'product_id' => $product->id,
            'order_item_id' => $item->id,
            'key' => License::generateKey(),
            'status' => 'active',
        ]);

        return $license;
    }

    private function createPurchasableProduct(): Product
    {
        return Product::create([
            'name' => 'Ready EA',
            'slug' => 'ready-ea',
            'platform' => 'MT5',
            'market' => 'XAUUSD',
            'description' => 'Ready to deliver.',
            'price_cents' => 10000,
            'sale_price_cents' => 4999,
            'file_path' => 'ea-files/ready.ex5',
            'is_active' => true,
        ]);
    }

    private function registerAndGetToken(): string
    {
        return $this->postJson('/api/auth/register', [
            'name' => 'Store Customer',
            'email' => 'customer@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ])->assertCreated()->json('token');
    }
}
