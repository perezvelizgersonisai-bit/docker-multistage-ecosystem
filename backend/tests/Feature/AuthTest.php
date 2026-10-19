<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_registrar_un_usuario_y_recibe_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Ana Cliente',
            'email' => 'ana@tienda.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['user', 'authorization' => ['token']]]);
    }

    public function test_puede_iniciar_sesion_con_credenciales_correctas(): void
    {
        User::factory()->create(['email' => 'ana@tienda.com', 'password' => bcrypt('password123')]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'ana@tienda.com',
            'password' => 'password123',
        ]);

        $response->assertOk()->assertJsonStructure(['data' => ['authorization' => ['token']]]);
    }

    public function test_rechaza_login_con_credenciales_incorrectas(): void
    {
        User::factory()->create(['email' => 'ana@tienda.com']);

        $response = $this->postJson('/api/auth/login', ['email' => 'ana@tienda.com', 'password' => 'mala']);

        $response->assertStatus(401);
    }

    public function test_puede_ver_su_perfil_con_token_valido(): void
    {
        $user = User::factory()->create(['email' => 'ana@tienda.com']);
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/auth/me');

        $response->assertOk()->assertJsonPath('data.email', 'ana@tienda.com');
    }
}
