import { NextResponse } from 'next/server';
import { Order } from '@/types';

// Almacenamiento temporal en memoria para simular backend
export const mockOrders: Order[] = [
  {
    id: 'ORD-98214',
    user_email: 'cliente@ejemplo.com',
    total: 47.49,
    status: 'paid',
    payment_method: 'Stripe (Visa ****4242)',
    created_at: '2026-09-27T14:30:00Z',
    items: [
      {
        id: '1',
        product_id: '1',
        product_name: 'Café Artesanal de El Boquerón',
        quantity: 1,
        price: 12.99
      },
      {
        id: '2',
        product_id: '2',
        product_name: 'Camisa Bordada de San Sebastián',
        quantity: 1,
        price: 34.50
      }
    ]
  }
];

export async function GET() {
  return NextResponse.json(mockOrders);
}

export async function POST(request: Request) {
  const body = await request.json();
  const { items, total, payment_method } = body;

  const newOrder: Order = {
    id: `ORD-${Math.floor(10000 + Math.random() * 90000)}`,
    user_email: 'usuario.demo@kodigo.org',
    total: total,
    status: 'paid',
    payment_method: payment_method || 'Stripe Test Card',
    created_at: new Date().toISOString(),
    items: items || []
  };

  mockOrders.unshift(newOrder);

  return NextResponse.json(newOrder, { status: 201 });
}
