<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $token = auth('api')->login($this->user);
        $this->headers = ['Authorization' => "Bearer {$token}"];

        // Sustituye la pasarela real de Stripe por un fake que no hace
        // llamadas de red, para que las pruebas corran de forma offline
        // y deterministica.
        $this->app->bind(PaymentGatewayInterface::class, function () {
            return new class implements PaymentGatewayInterface
            {
                public function charge(float $amount, string $currency, string $paymentMethodId, string $description = ''): array
                {
                    return [
                        'id' => 'pi_fake_'.uniqid(),
                        'status' => 'succeeded',
                        'amount' => $amount,
                        'currency' => $currency,
                    ];
                }
            };
        });
    }

    public function test_no_puede_crear_orden_sin_token(): void
    {
        $product = Product::factory()->create();

        $this->postJson('/api/orders', ['items' => [['product_id' => $product->id, 'quantity' => 1]]])
            ->assertStatus(401);
    }

    public function test_puede_crear_una_orden_y_descuenta_stock(): void
    {
        $product = Product::factory()->create(['price' => 10.00, 'stock' => 5]);

        $response = $this->withHeaders($this->headers)->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total', 20);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
    }

    public function test_falla_al_crear_orden_con_stock_insuficiente(): void
    {
        $product = Product::factory()->create(['stock' => 1]);

        $response = $this->withHeaders($this->headers)->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 5]],
        ]);

        $response->assertStatus(422);
    }

    public function test_puede_listar_su_historial_de_ordenes(): void
    {
        $product = Product::factory()->create();
        $this->withHeaders($this->headers)->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $response = $this->withHeaders($this->headers)->getJson('/api/orders');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_puede_pagar_una_orden_pendiente(): void
    {
        $product = Product::factory()->create(['price' => 25.00, 'stock' => 10]);

        $order = $this->withHeaders($this->headers)->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->json('data');

        $response = $this->withHeaders($this->headers)->postJson("/api/orders/{$order['id']}/pay", [
            'payment_method' => 'pm_card_visa',
        ]);

        $response->assertOk()->assertJsonPath('data.order.status', 'paid');
        $this->assertDatabaseHas('payments', ['order_id' => $order['id'], 'status' => 'succeeded']);
    }

    public function test_no_puede_pagar_una_orden_ya_pagada(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $order = $this->withHeaders($this->headers)->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->json('data');

        $this->withHeaders($this->headers)->postJson("/api/orders/{$order['id']}/pay", ['payment_method' => 'pm_card_visa']);

        $response = $this->withHeaders($this->headers)->postJson("/api/orders/{$order['id']}/pay", ['payment_method' => 'pm_card_visa']);

        $response->assertStatus(409);
    }

    public function test_no_puede_ver_orden_de_otro_usuario(): void
    {
        $product = Product::factory()->create();
        $order = $this->withHeaders($this->headers)->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->json('data');

        $otherUser = User::factory()->create();
        $otherToken = auth('api')->login($otherUser);

        $response = $this->withHeader('Authorization', "Bearer {$otherToken}")
            ->getJson("/api/orders/{$order['id']}");

        $response->assertStatus(404);
    }
}
