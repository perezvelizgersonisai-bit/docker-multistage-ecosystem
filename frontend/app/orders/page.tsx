import { Suspense } from 'react';
import { Order } from '@/types';
import { PackageCheck, Clock, CheckCircle } from 'lucide-react';

async function getOrders(): Promise<Order[]> {
  const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';
  const res = await fetch(`${baseUrl}/api/orders`, {
    cache: 'no-store', // Refresco instantáneo tras mutación revalidatePath
  });
  if (!res.ok) throw new Error('Error al consultar el historial de órdenes');
  return res.json();
}

async function OrdersList() {
  const orders = await getOrders();

  if (orders.length === 0) {
    return (
      <div class="text-center py-16 bg-white rounded-3xl border border-slate-200">
        <p class="text-slate-500 font-semibold">No se encontraron órdenes registradas.</p>
      </div>
    );
  }

  return (
    <div class="space-y-6">
      {orders.map((order) => (
        <div
          key={order.id}
          class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4"
        >
          <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2 border-b border-slate-100 pb-4">
            <div>
              <span class="text-xs font-bold text-indigo-600 font-mono">
                #{order.id}
              </span>
              <p class="text-xs text-slate-400">
                Fecha: {new Date(order.created_at).toLocaleString('es-SV')}
              </p>
            </div>

            <div class="flex items-center gap-3">
              <span class="inline-flex items-center gap-1 text-xs px-3 py-1 bg-emerald-50 text-emerald-700 font-bold rounded-full border border-emerald-100">
                <CheckCircle class="w-3.5 h-3.5" />
                Completada ({order.status})
              </span>
              <span class="text-base font-black text-slate-900">
                ${order.total.toFixed(2)}
              </span>
            </div>
          </div>

          <div class="space-y-2">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">
              Productos Comprados:
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              {order.items.map((item) => (
                <div
                  key={item.id}
                  class="bg-slate-50 p-2.5 rounded-xl text-xs flex justify-between items-center"
                >
                  <span class="font-medium text-slate-700 truncate max-w-[200px]">
                    {item.quantity}x {item.product_name}
                  </span>
                  <span class="font-bold text-slate-900">
                    ${(item.price * item.quantity).toFixed(2)}
                  </span>
                </div>
              ))}
            </div>
          </div>

          <div class="pt-2 text-[11px] text-slate-400 flex justify-between">
            <span>Método: {order.payment_method}</span>
            <span>Usuario: {order.user_email}</span>
          </div>
        </div>
      ))}
    </div>
  );
}

export default function OrdersPage() {
  return (
    <div class="max-w-4xl mx-auto space-y-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-black text-slate-900 flex items-center gap-2">
            <PackageCheck class="w-6 h-6 text-indigo-600" />
            <span>Historial de Órdenes</span>
          </h1>
          <p class="text-xs text-slate-500 mt-1">
            Vista protegida consumiendo API en Server Components con Suspense y revalidación de caché
          </p>
        </div>
      </div>

      <Suspense
        fallback={
          <div class="space-y-4">
            {[1, 2].map((n) => (
              <div
                key={n}
                class="bg-white h-40 rounded-3xl border border-slate-200 animate-pulse p-6"
              ></div>
            ))}
          </div>
        }
      >
        <OrdersList />
      </Suspense>
    </div>
  );
}
