'use server';

import { cookies } from 'next/headers';
import { revalidatePath } from 'next/cache';

export async function loginAction(formData: FormData) {
  const email = formData.get('email') as string;
  const password = formData.get('password') as string;

  const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';

  try {
    const res = await fetch(`${baseUrl}/api/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    });

    if (!res.ok) {
      const err = await res.json();
      return { error: err.message || 'Error al iniciar sesión' };
    }

    const data = await res.json();
    
    // Cookie httpOnly
    const cookieStore = await cookies();
    cookieStore.set('token', data.token, {
      httpOnly: true,
      path: '/',
      secure: process.env.NODE_ENV === 'production',
    });

    revalidatePath('/');
    return { success: true, user: data.user };
  } catch (error) {
    return { error: 'Error de conexión con el servidor' };
  }
}

export async function registerAction(formData: FormData) {
  const name = formData.get('name') as string;
  const email = formData.get('email') as string;
  const password = formData.get('password') as string;

  const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';

  try {
    const res = await fetch(`${baseUrl}/api/auth/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, email, password }),
    });

    if (!res.ok) {
      const err = await res.json();
      return { error: err.message || 'Error al registrar usuario' };
    }

    const data = await res.json();

    const cookieStore = await cookies();
    cookieStore.set('token', data.token, {
      httpOnly: true,
      path: '/',
      secure: process.env.NODE_ENV === 'production',
    });

    revalidatePath('/');
    return { success: true, user: data.user };
  } catch (error) {
    return { error: 'Error de conexión con el servidor' };
  }
}

export async function logoutAction() {
  const cookieStore = await cookies();
  cookieStore.delete('token');
  revalidatePath('/');
}

export async function getAuthToken() {
  const cookieStore = await cookies();
  return cookieStore.get('token')?.value;
}
