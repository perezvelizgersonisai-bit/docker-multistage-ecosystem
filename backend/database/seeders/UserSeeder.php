<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Crea un cliente de prueba para poder iniciar sesión de inmediato.
 *
 * Credenciales de prueba:
 *   email:    cliente@tienda.com
 *   password: password123
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'cliente@tienda.com'],
            [
                'name' => 'Cliente Demo',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
