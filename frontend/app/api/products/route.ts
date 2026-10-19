import { NextResponse } from 'next/server';
import { Product } from '@/types';

export const mockProducts: Product[] = [
  {
    id: '1',
    name: 'Café Artesanal de El Boquerón',
    description: 'Café de estricta altura cultivado en las faldas del Volcán de San Salvador. Notas de chocolate y frutos rojos.',
    price: 12.99,
    category: 'Alimentos y Bebidas',
    image: 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?auto=format&fit=crop&w=800&q=80',
    stock: 25,
  },
  {
    id: '2',
    name: 'Camisa Bordada de San Sebastián',
    description: 'Camisa confeccionada a mano por artesanos salvadoreños con técnicas de telar de palanca tradicionales.',
    price: 34.50,
    category: 'Artesanías y Moda',
    image: 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=800&q=80',
    stock: 15,
  },
  {
    id: '3',
    name: 'Tabla de Surf Pro Surf City',
    description: 'Edición limitada inspirada en las olas de El Tunco y Punta Roca. Diseñada para alto rendimiento.',
    price: 499.00,
    category: 'Deportes y Playa',
    image: 'https://images.unsplash.com/photo-1502680390469-be75c86b636f?auto=format&fit=crop&w=800&q=80',
    stock: 5,
  },
  {
    id: '4',
    name: 'Hamaca de Algodón Cacaopera',
    description: 'Hamaca matrimonial tejida en hilaza de algodón 100% natural, súper cómoda y resistente para exteriores.',
    price: 65.00,
    category: 'Hogar y Decoración',
    image: 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',
    stock: 10,
  },
  {
    id: '5',
    name: 'Miel de Abeja Silvestre del Imposible',
    description: 'Miel pura y multifloral extraída de las reservas biológicas del Parque Nacional El Imposible.',
    price: 8.75,
    category: 'Alimentos y Bebidas',
    image: 'https://images.unsplash.com/photo-1587049352847-4a222e784d38?auto=format&fit=crop&w=800&q=80',
    stock: 40,
  }
];

export async function GET() {
  // Simular pequeña latencia de servidor
  await new Promise((res) => setTimeout(res, 300));
  return NextResponse.json(mockProducts);
}
