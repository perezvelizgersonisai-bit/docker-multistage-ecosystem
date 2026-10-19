'use server';

import { revalidatePath } from 'next/cache';
import { CartItem } from '@/types';

export async function createOrderAndPayAction(cartItems: CartItem[], paymentDetails: { cardHolder: string }) {
  const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';

  const total = cartItems.reduce(
    (sum, item) => sum + item.product.price * item.quantity,
    0
  );

  const formattedItems = cartItems.map((item) => ({
    id: `item-${Date.now()}-${item.product.id}`,
    product_id: item.product.id,
    product_name: item.product.name,
    quantity: item.quantity,
    price: item.product.price,
  }));

  try {
    // Mutación asíncrona enviada al servidor
    const res = await fetch(`${baseUrl}/api/orders`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        items: formattedItems,
        total: total,
        payment_method: `Stripe Credit Card (${paymentDetails.cardHolder})`,
      }),
    });

    if (!res.ok) {
      return { error: 'No se pudo procesar el pago con Stripe' };
    }

    const order = await res.json();

    // Revalidación de caché en el servidor para actualizar el historial inmediatamente
    revalidatePath('/orders');
    revalidatePath('/cart');

    return { success: true, order };
  } catch (e) {
    return { error: 'Error de servidor al procesar la mutación de la orden' };
  }
}
