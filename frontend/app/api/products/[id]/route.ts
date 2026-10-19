import { NextResponse } from 'next/server';
import { mockProducts } from '../route';

export async function GET(
  request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id } = await params;
  const product = mockProducts.find((p) => p.id === id);

  if (!product) {
    return NextResponse.json(
      { message: 'Producto no encontrado' },
      { status: 404 }
    );
  }

  return NextResponse.json(product);
}
