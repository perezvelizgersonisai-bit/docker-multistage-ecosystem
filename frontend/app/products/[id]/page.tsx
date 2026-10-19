import { notFound } from 'next/navigation';
import { Product } from '@/types';
import AddToCartButton from '@/components/AddToCartButton';
import Link from 'next/link';
import { ArrowLeft, ShieldCheck, Truck } from 'lucide-react';

async function getProduct(id: string): Promise<Product | null> {
  const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';
  const res = await fetch(`${baseUrl}/api/products/${id}`, { cache: 'no-store' });
  if (!res.ok) return null;
  return res.json();
}

export default async function ProductDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const product = await getProduct(id);

  if (!product) {
    notFound();
  }

  return (
    <div class="max-w-4xl mx-auto space-y-6">
      <Link
        href="/"
        class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-indigo-600 transition-colors"
      >
        <ArrowLeft class="w-4 h-4" />
        Volver al Catálogo
      </Link>

      <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm grid grid-cols-1 md:grid-cols-2">
        <div class="relative aspect-square bg-slate-100">
          <img
            src={product.image}
            alt={product.name}
            class="w-full h-full object-cover"
          />
        </div>

        <div class="p-8 flex flex-col justify-between space-y-6">
          <div class="space-y-4">
            <span class="text-xs font-bold px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full inline-block">
              {product.category}
            </span>
            <h1 class="text-2xl font-black text-slate-900">{product.name}</h1>
            <p class="text-sm text-slate-600 leading-relaxed">
              {product.description}
            </p>
          </div>

          <div class="space-y-6 pt-6 border-t border-slate-100">
            <div class="flex items-baseline gap-2">
              <span class="text-3xl font-black text-slate-900">
                ${product.price.toFixed(2)}
              </span>
              <span class="text-xs text-emerald-600 font-bold">
                {product.stock} unidades en stock
              </span>
            </div>

            <AddToCartButton product={product} />

            <div class="grid grid-cols-2 gap-4 pt-4 text-xs text-slate-500 border-t border-slate-100">
              <div class="flex items-center gap-2">
                <Truck class="w-4 h-4 text-indigo-600" />
                <span>Envío express</span>
              </div>
              <div class="flex items-center gap-2">
                <ShieldCheck class="w-4 h-4 text-indigo-600" />
                <span>Garantía oficial</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
