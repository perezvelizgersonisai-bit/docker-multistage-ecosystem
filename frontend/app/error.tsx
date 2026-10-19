'use client';

import { useEffect } from 'react';

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  return (
    <div class="min-h-[50vh] flex flex-col items-center justify-center text-center gap-4 bg-white p-8 rounded-3xl border border-red-100 shadow-sm max-w-lg mx-auto my-12">
      <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center font-bold text-xl">
        !
      </div>
      <h2 class="text-xl font-bold text-slate-900">
        Ha ocurrido un error en el segmento
      </h2>
      <p class="text-sm text-slate-500">
        {error.message || 'No se pudo cargar la información del servidor.'}
      </p>
      <button
        onClick={() => reset()}
        class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all"
      >
        Reintentar Petición
      </button>
    </div>
  );
}
