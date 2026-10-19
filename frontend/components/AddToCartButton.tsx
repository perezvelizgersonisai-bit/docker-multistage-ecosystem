'use client';

import { useCart } from '@/context/CartContext';
import { Product } from '@/types';
import { ShoppingBag } from 'lucide-react';
import { useState } from 'react';

export default function AddToCartButton({ product }: { product: Product }) {
  const { addToCart } = useCart();
  const [added, setAdded] = useState(false);

  const handleAdd = () => {
    addToCart(product);
    setAdded(true);
    setTimeout(() => setAdded(false), 1500);
  };

  return (
    <button
      onClick={handleAdd}
      class={`px-3 py-1.5 text-xs font-bold rounded-xl flex items-center gap-1.5 transition-all ${
        added
          ? 'bg-emerald-600 text-white'
          : 'bg-indigo-600 text-white hover:bg-indigo-700 active:scale-95'
      }`}
    >
      <ShoppingBag class="w-3.5 h-3.5" />
      <span>{added ? '¡Agregado!' : 'Agregar'}</span>
    </button>
  );
}
