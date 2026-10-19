'use client';

import { useCart } from '@/context/CartContext';
import Link from 'next/link';
import { Trash2, ArrowRight, ShoppingBag } from 'lucide-react';

export default function CartPage() {
  const { cart, removeFromCart, clearCart, totalPrice } = useCart();

  if (cart.length === 0) {
    return (
      <div class="max-w-md mx-auto my-16 text-center bg-white p-8 rounded-3xl border border-slate-200 space-y-4">
        <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto">
          <ShoppingBag class="w-8 h-8" />
        </div>
        <h1 class="text-xl font-bold text-slate-900">Tu carrito está vacío</h1>
        <p class="text-xs text-slate-500">
          Explora nuestro catálogo para agregar productos a tu orden.
        </p>
        <Link
          href="/"
          class="inline-block px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition-colors"
        >
          Explorar Catálogo
        </Link>
      </div>
    );
  }

  return (
    <div class="max-w-4xl mx-auto space-y-8">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-black text-slate-900">Carrito de Compras</h1>
        <button
          onClick={clearCart}
          class="text-xs font-bold text-red-500 hover:underline"
        >
          Vaciar Carrito
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-4">
          {cart.map((item) => (
            <div
              key={item.product.id}
              class="bg-white p-4 rounded-2xl border border-slate-200 flex items-center gap-4 justify-between"
            >
              <img
                src={item.product.image}
                alt={item.product.name}
                class="w-16 h-16 object-cover rounded-xl bg-slate-100"
              />
              <div class="flex-grow min-w-0">
                <h3 class="text-sm font-bold text-slate-900 truncate">
                  {item.product.name}
                </h3>
                <p class="text-xs text-slate-500">
                  Cant: {item.quantity} x ${item.product.price.toFixed(2)}
                </p>
              </div>
              <div class="text-right">
                <span class="text-sm font-black text-slate-900 block">
                  ${(item.product.price * item.quantity).toFixed(2)}
                </span>
                <button
                  onClick={() => removeFromCart(item.product.id)}
                  class="text-slate-400 hover:text-red-500 transition-colors p-1"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          ))}
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 h-fit space-y-6">
          <h2 class="text-lg font-bold text-slate-900">Resumen de Orden</h2>

          <div class="space-y-2 text-xs text-slate-600 border-t border-b border-slate-100 py-4">
            <div class="flex justify-between">
              <span>Subtotal</span>
              <span>${totalPrice.toFixed(2)}</span>
            </div>
            <div class="flex justify-between">
              <span>Envío</span>
              <span class="text-emerald-600 font-bold">Gratis</span>
            </div>
            <div class="flex justify-between text-sm font-black text-slate-900 pt-2 border-t border-slate-100">
              <span>Total</span>
              <span>${totalPrice.toFixed(2)}</span>
            </div>
          </div>

          <Link
            href="/checkout"
            class="w-full py-3 bg-indigo-600 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 hover:bg-indigo-700 transition-colors"
          >
            <span>Proceder al Pago (Stripe)</span>
            <ArrowRight class="w-4 h-4" />
          </Link>
        </div>
      </div>
    </div>
  );
}
