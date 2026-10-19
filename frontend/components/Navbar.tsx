'use client';

import Link from 'next/link';
import { useCart } from '@/context/CartContext';
import { ShoppingBag, User, LogOut, PackageCheck, Store } from 'lucide-react';
import { logoutAction } from '@/app/actions/auth';

export default function Navbar() {
  const { totalItems } = useCart();

  return (
    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <Link href="/" class="flex items-center gap-2 font-black text-xl text-indigo-600">
          <Store class="w-6 h-6" />
          <span>TiendaSV</span>
        </Link>

        <nav class="flex items-center gap-6 text-sm font-semibold text-slate-600">
          <Link href="/" class="hover:text-indigo-600 transition-colors">
            Catálogo
          </Link>
          <Link href="/orders" class="flex items-center gap-1 hover:text-indigo-600 transition-colors">
            <PackageCheck class="w-4 h-4" />
            Mis Órdenes
          </Link>
          <Link href="/cart" class="relative flex items-center gap-1.5 bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-full hover:bg-indigo-100 transition-colors">
            <ShoppingBag class="w-4 h-4" />
            <span>Carrito</span>
            {totalItems > 0 && (
              <span class="bg-indigo-600 text-white text-xs px-2 py-0.5 rounded-full font-bold">
                {totalItems}
              </span>
            )}
          </Link>
          <Link href="/login" class="flex items-center gap-1 text-slate-500 hover:text-slate-900 transition-colors">
            <User class="w-4 h-4" />
            Acceso
          </Link>
          <button
            onClick={() => logoutAction()}
            title="Cerrar sesión"
            class="text-slate-400 hover:text-red-500 transition-colors"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </nav>
      </div>
    </header>
  );
}
