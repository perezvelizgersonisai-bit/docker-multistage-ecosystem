'use client';

import { useState } from 'react';
import { useCart } from '@/context/CartContext';
import { createOrderAndPayAction } from '@/app/actions/order';
import { useRouter } from 'next/navigation';
import { CreditCard, Lock, CheckCircle2 } from 'lucide-react';

export default function CheckoutPage() {
  const { cart, totalPrice, clearCart } = useCart();
  const [loading, setLoading] = useState(false);
  const [completedOrder, setCompletedOrder] = useState<any>(null);
  const [error, setError] = useState<string | null>(null);
  const router = useRouter();

  const handleStripePayment = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    const formData = new FormData(e.currentTarget);
    const cardHolder = formData.get('cardHolder') as string;

    const res = await createOrderAndPayAction(cart, { cardHolder });
    setLoading(false);

    if (res.error) {
      setError(res.error);
    } else if (res.success) {
      setCompletedOrder(res.order);
      clearCart();
    }
  };

  if (completedOrder) {
    return (
      <div class="max-w-md mx-auto my-12 bg-white p-8 rounded-3xl border border-slate-200 text-center space-y-6">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto">
          <CheckCircle2 class="w-10 h-10" />
        </div>
        <div class="space-y-2">
          <h1 class="text-2xl font-black text-slate-900">¡Pago Confirmado!</h1>
          <p class="text-xs text-slate-500">
            Tu orden #{completedOrder.id} ha sido procesada exitosamente en Stripe.
          </p>
        </div>

        <div class="bg-slate-50 p-4 rounded-2xl text-xs space-y-2 text-left">
          <div class="flex justify-between">
            <span class="text-slate-500">ID Orden:</span>
            <span class="font-bold text-slate-900">{completedOrder.id}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Total pagado:</span>
            <span class="font-bold text-emerald-600">${completedOrder.total.toFixed(2)}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Método de Pago:</span>
            <span class="font-semibold text-slate-700">{completedOrder.payment_method}</span>
          </div>
        </div>

        <button
          onClick={() => router.push('/orders')}
          class="w-full py-3 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition-colors"
        >
          Ver Historial de Órdenes
        </button>
      </div>
    );
  }

  if (cart.length === 0) {
    return (
      <div class="text-center py-16">
        <p class="text-slate-500 font-semibold">No tienes ítems en el carrito para procesar pago.</p>
      </div>
    );
  }

  return (
    <div class="max-w-3xl mx-auto space-y-8">
      <div class="text-center space-y-2">
        <h1 class="text-2xl font-black text-slate-900">Pasarela de Pago Stripe</h1>
        <p class="text-xs text-slate-500">
          Proceso de Checkout con Mutación en Servidor (Server Actions) y Revalidación de Caché
        </p>
      </div>

      {error && (
        <div class="p-3 bg-red-50 text-red-600 text-xs font-semibold rounded-xl text-center">
          {error}
        </div>
      )}

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 space-y-6">
          <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <CreditCard class="w-4 h-4 text-indigo-600" />
            <span>Datos de Tarjeta Stripe</span>
          </h2>

          <form onSubmit={handleStripePayment} class="space-y-4">
            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700">Titular de la Tarjeta</label>
              <input
                type="text"
                name="cardHolder"
                required
                defaultValue="Juan Pérez"
                class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-600 outline-none"
              />
            </div>

            <div class="space-y-1">
              <label class="text-xs font-bold text-slate-700">Número de Tarjeta (Stripe Test)</label>
              <input
                type="text"
                required
                defaultValue="4242 4242 4242 4242"
                class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-indigo-600 outline-none"
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700">Vencimiento</label>
                <input
                  type="text"
                  required
                  defaultValue="12/28"
                  class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-600 outline-none"
                />
              </div>
              <div class="space-y-1">
                <label class="text-xs font-bold text-slate-700">CVC</label>
                <input
                  type="text"
                  required
                  defaultValue="123"
                  class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-600 outline-none"
                />
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
              class="w-full py-3 bg-indigo-600 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 hover:bg-indigo-700 transition-colors disabled:opacity-50"
            >
              <Lock class="w-4 h-4" />
              <span>{loading ? 'Procesando en Stripe...' : `Pagar $${totalPrice.toFixed(2)}`}</span>
            </button>
          </form>
        </div>

        <div class="bg-slate-50 p-6 rounded-3xl border border-slate-200 space-y-4">
          <h2 class="text-sm font-bold text-slate-900">Resumen de la Compra</h2>

          <div class="space-y-3">
            {cart.map((item) => (
              <div key={item.product.id} class="flex justify-between items-center text-xs">
                <span class="truncate max-w-[180px] text-slate-600">
                  {item.quantity}x {item.product.name}
                </span>
                <span class="font-bold text-slate-900">
                  ${(item.product.price * item.quantity).toFixed(2)}
                </span>
              </div>
            ))}
          </div>

          <div class="pt-4 border-t border-slate-200 flex justify-between items-center">
            <span class="text-xs font-bold text-slate-700">Monto Total</span>
            <span class="text-lg font-black text-slate-900">${totalPrice.toFixed(2)}</span>
          </div>
        </div>
      </div>
    </div>
  );
}
