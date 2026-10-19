import { NextResponse } from 'next/server';

export async function POST(request: Request) {
  const body = await request.json();
  const { name, email, password } = body;

  if (!name || !email || !password) {
    return NextResponse.json(
      { message: 'Todos los campos son requeridos' },
      { status: 400 }
    );
  }

  const token = `fake-jwt-token-for-${email}-${Date.now()}`;

  const response = NextResponse.json({
    message: 'Usuario registrado con éxito',
    user: {
      id: `usr-${Date.now()}`,
      name: name,
      email: email,
    },
    token: token,
  });

  response.cookies.set({
    name: 'token',
    value: token,
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    path: '/',
    maxAge: 60 * 60 * 24 * 7,
  });

  return response;
}
