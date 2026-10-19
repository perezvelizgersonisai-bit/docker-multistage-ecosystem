import './globals.css';
import { Metadata } from 'next';
import Link from 'next/link';
import { CartProvider } from '@/context/CartContext';
import Navbar from '@/components/Navbar';

export const metadata: Metadata = {
  title: 'E-commerce El Salvador | Next.js App Router',
  description: 'Tienda en línea desarrollada en Next.js 16+ consumiendo API Laravel + Stripe',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="es">
      <body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col font-sans">
        <CartProvider>
          <Navbar />
          <main class="flex-grow max-w-7xl mx-auto px-6 py-8 w-full">
            {children}
          </main>
          <footer class="bg-white border-t border-slate-200 py-6 text-center text-sm text-slate-500">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
              <p>© 2026 E-commerce El Salvador - Next.js 16+ App Router</p>
              <p>Desarrollado con Server Components & Server Actions</p>
            </div>
          </footer>
        </CartProvider>
      </body>
    </html>
  );
}
