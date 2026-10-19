import { Suspense } from 'react';
import Link from 'next/link';
import { Product } from '@/types';
import AddToCartButton from '@/components/AddToCartButton';

async function getProducts(): Promise<Product[]> {
  const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';
  const res = await fetch(`${baseUrl}/api/products`, {
    cache: 'no-store', // Lectura en servidor siempre fresca
  });
  if (!res.ok) throw new Error('Error al consultar el catálogo de productos');
  return res.json();
}

async function ProductList() {
  const products = await getProducts();

  return (
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      {products.map((product) => (
        <div
          key={product.id}
          class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition-all flex flex-col group"
        >
          <div class="relative aspect-[4/3] bg-slate-100 overflow-hidden">
            <img
              src={product.image}
              alt={product.name}
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            />
            <span class="absolute top-4 left-4 bg-white/90 backdrop-blur-md text-indigo-600 text-xs px-3 py-1 rounded-full font-bold">
              {product.category}
            </span>
          </div>

          <div class="p-6 flex-grow flex flex-col justify-between gap-4">
            <div>
              <h2 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                {product.name}
              </h2>
              <p class="text-xs text-slate-500 line-clamp-2 mt-1">
                {product.description}
              </p>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
              <div>
                <span class="text-[10px] text-slate-400 font-bold block uppercase">
                  Precio
                </span>
                <span class="text-lg font-black text-slate-900">
                  ${product.price.toFixed(2)}
                </span>
              </div>

              <div class="flex items-center gap-2">
                <Link
                  href={`/products/${product.id}`}
                  class="px-3 py-1.5 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 transition-colors"
                >
                  Ver
                </Link>
                <AddToCartButton product={product} />
              </div>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
}

export default function HomePage() {
  return (
    <div class="space-y-8">
      <div class="text-center max-w-2xl mx-auto space-y-3">
        <span class="text-xs font-bold px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full">
          Server Components + Next.js App Router
        </span>
        <h1 class="text-4xl font-black text-slate-900 tracking-tight">
          Catálogo de Productos E-commerce
        </h1>
        <p class="text-sm text-slate-600">
          Explora los productos consumidos en vivo desde la API REST. Optimizado con Web Vitals y renderizado híbrido.
        </p>
      </div>

      {/* Uso obligatorio de Suspense en sección pesada */}
      <Suspense
        fallback={
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {[1, 2, 3].map((n) => (
              <div
                key={n}
                class="bg-white h-80 rounded-3xl border border-slate-200 animate-pulse p-6 flex flex-col justify-between"
              >
                <div class="bg-slate-200 h-40 rounded-2xl"></div>
                <div class="space-y-2">
                  <div class="bg-slate-200 h-4 w-3/4 rounded"></div>
                  <div class="bg-slate-200 h-3 w-1/2 rounded"></div>
                </div>
              </div>
            ))}
          </div>
        }
      >
        <ProductList />
      </Suspense>
    </div>
  );
}
