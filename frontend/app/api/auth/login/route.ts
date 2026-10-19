import { NextResponse } from 'next/server';

export async function POST(request: Request) {
  const body = await request.json();
  const { email, password } = body;

  if (!email || !password) {
    return NextResponse.json(
      { message: 'Email y contraseña requeridos' },
      { status: 400 }
    );
  }

  // Simular token JWT generado por backend Laravel
  const token = `fake-jwt-token-for-${email}-${Date.now()}`;

  const response = NextResponse.json({
    message: 'Autenticación exitosa',
    user: {
      id: 'usr-123',
      name: email.split('@')[0],
      email: email,
    },
    token: token,
  });

  // Guardar token seguro en cookie httpOnly
  response.cookies.set({
    name: 'token',
    value: token,
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    path: '/',
    maxAge: 60 * 60 * 24 * 7, // 1 semana
  });

  return response;
}
