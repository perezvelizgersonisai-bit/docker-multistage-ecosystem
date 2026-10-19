'use client';

import { useState } from 'react';
import { registerAction } from '@/app/actions/auth';
import { useRouter } from 'next/navigation';
import Link from 'next/link';

export default function RegisterPage() {
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const router = useRouter();

  async function handleSubmit(formData: FormData) {
    setLoading(true);
    setError(null);
    const res = await registerAction(formData);
    setLoading(false);

    if (res?.error) {
      setError(res.error);
    } else if (res?.success) {
      router.push('/orders');
      router.refresh();
    }
  }

  return (
    <div class="max-w-md mx-auto my-12 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
      <div class="text-center space-y-2">
        <h1 class="text-2xl font-black text-slate-900">Crear Cuenta</h1>
        <p class="text-xs text-slate-500">
          Registro consumiendo la API de autenticación
        </p>
      </div>

      {error && (
        <div class="p-3 bg-red-50 text-red-600 text-xs font-semibold rounded-xl text-center">
          {error}
        </div>
      )}

      <form action={handleSubmit} class="space-y-4">
        <div class="space-y-1">
          <label class="text-xs font-bold text-slate-700">Nombre Completo</label>
          <input
            type="text"
            name="name"
            required
            placeholder="Juan Pérez"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600"
          />
        </div>

        <div class="space-y-1">
          <label class="text-xs font-bold text-slate-700">Correo Electrónico</label>
          <input
            type="email"
            name="email"
            required
            placeholder="juan@ejemplo.com"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600"
          />
        </div>

        <div class="space-y-1">
          <label class="text-xs font-bold text-slate-700">Contraseña</label>
          <input
            type="password"
            name="password"
            required
            placeholder="••••••••"
            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600"
          />
        </div>

        <button
          type="submit"
          disabled={loading}
          class="w-full py-3 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition-colors disabled:opacity-50"
        >
          {loading ? 'Registrando...' : 'Registrar Cuenta'}
        </button>
      </form>

      <div class="text-center text-xs text-slate-500 pt-4 border-t border-slate-100">
        ¿Ya tienes cuenta?{' '}
        <Link href="/login" class="text-indigo-600 font-bold hover:underline">
          Ingresa aquí
        </Link>
      </div>
    </div>
  );
}
