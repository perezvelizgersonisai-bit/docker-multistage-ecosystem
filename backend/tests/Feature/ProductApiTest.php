<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authHeader(): array
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_puede_listar_productos_sin_autenticacion(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_puede_ver_un_producto_sin_autenticacion(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertOk()->assertJsonPath('data.id', $product->id);
    }

    public function test_no_puede_crear_producto_sin_token(): void
    {
        $this->postJson('/api/products', ['name' => 'X'])->assertStatus(401);
    }

    public function test_puede_crear_producto_con_token_valido(): void
    {
        $payload = ['name' => 'Cable USB-C', 'price' => 9.99, 'stock' => 10, 'sku' => 'CAB-0001'];

        $response = $this->withHeaders($this->authHeader())->postJson('/api/products', $payload);

        $response->assertCreated()->assertJsonPath('data.sku', 'CAB-0001');
    }

    public function test_falla_al_crear_producto_sin_campos_obligatorios(): void
    {
        $response = $this->withHeaders($this->authHeader())->postJson('/api/products', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['name', 'price', 'stock', 'sku']);
    }

    public function test_puede_actualizar_un_producto(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/products/{$product->id}", ['stock' => 20]);

        $response->assertOk()->assertJsonPath('data.stock', 20);
    }

    public function test_puede_eliminar_un_producto(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeaders($this->authHeader())->deleteJson("/api/products/{$product->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
