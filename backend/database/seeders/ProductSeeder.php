<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Teclado Mecánico RGB', 'description' => 'Teclado mecánico con switches rojos e iluminación RGB.', 'price' => 45.99, 'stock' => 30, 'sku' => 'TEC-0001'],
            ['name' => 'Mouse Inalámbrico', 'description' => 'Mouse óptico inalámbrico 2.4GHz, 1600 DPI.', 'price' => 15.50, 'stock' => 60, 'sku' => 'MOU-0001'],
            ['name' => 'Monitor 24" Full HD', 'description' => 'Monitor LED de 24 pulgadas, resolución 1920x1080.', 'price' => 129.99, 'stock' => 15, 'sku' => 'MON-0001'],
            ['name' => 'Audífonos Bluetooth', 'description' => 'Audífonos over-ear con cancelación de ruido.', 'price' => 39.90, 'stock' => 45, 'sku' => 'AUD-0001'],
            ['name' => 'Webcam Full HD', 'description' => 'Cámara web 1080p con micrófono integrado.', 'price' => 25.00, 'stock' => 25, 'sku' => 'CAM-0001'],
            ['name' => 'Disco SSD 500GB', 'description' => 'Unidad de estado sólido SATA III, 500GB.', 'price' => 49.99, 'stock' => 20, 'sku' => 'SSD-0001'],
        ];

        foreach ($products as $product) {
            Product::query()->firstOrCreate(['sku' => $product['sku']], $product);
        }
    }
}
