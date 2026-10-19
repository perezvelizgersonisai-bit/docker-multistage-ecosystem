# 🛒 E-commerce Frontend en Next.js 16+ (App Router) & Stripe API

Este repositorio contiene la solución completa para la actividad evaluada **"Optimización Avanzada de Rendimiento: Dominio de Web Vitals y Mutaciones Asíncronas en el Servidor"**.

Desarrollado con **Next.js 16+ (App Router)**, **TypeScript**, **Tailwind CSS**, **Server Components**, **Server Actions** y mutaciones en servidor con revalidación de caché (`revalidatePath`).

---

## 🚀 Características y Funcionalidades Obligatorias

1. **Catálogo Público de Productos**:
   - Lectura optimizada en servidor mediante **Server Components** (`/`).
   - Vista detallada de cada producto (`/products/[id]`).
   - Sección pesada envuelta en **`<Suspense>`** con Skeleton Loaders.
2. **Autenticación Segura**:
   - Registro (`/register`) y Login (`/login`) consumiendo la API REST.
   - Token de sesión guardado de forma segura en cookies **`httpOnly`** mediante Server Actions y Route Handlers.
3. **Carrito de Compras**:
   - Gestión de estado local reactivo en cliente (`/cart`).
4. **Pago con Stripe & Mutaciones**:
   - Flujo completo de Checkout (`/checkout`).
   - Mutación asíncrona enviada al servidor ejecutando pago simulación Stripe.
   - Actualización inmediata del servidor usando `revalidatePath('/orders')`.
5. **Historial de Compras Protegido**:
   - Lista protegida de órdenes realizadas por el usuario (`/orders`).
6. **Resiliencia y Rendimiento (Web Vitals)**:
   - `app/loading.tsx` para estados de carga fluidos.
   - `app/error.tsx` para captura de errores del segmento.

---

## ⚙️ Configuración del Entorno (`.env.example`)

Crea un archivo `.env.local` basado en el archivo `.env.example`:

```env
# URL Base de la API REST (Laravel 12 / Next.js API Routes)
NEXT_PUBLIC_API_URL=http://localhost:3000
```

---

## 🛠️ Pasos para Ejecutar el Proyecto

1. **Clonar e instalar dependencias**:
   ```bash
   git clone <URL_DE_TU_REPOSITORIO>
   cd ecommerce-nextjs
   npm install
   ```

2. **Iniciar el servidor de desarrollo**:
   ```bash
   npm run dev
   ```

3. **Abrir en el navegador**:
   Navega a [http://localhost:3000](http://localhost:3000).

---

## 🛣️ Rutas Implementadas

| Ruta | Descripción | Tipo de Componente |
| :--- | :--- | :--- |
| `/` | Catálogo público de productos | Server Component + Suspense |
| `/products/[id]` | Vista detallada del producto | Server Component |
| `/login` | Formulario de autenticación | Client Component + Server Action |
| `/register` | Formulario de registro de usuario | Client Component + Server Action |
| `/cart` | Carrito de compras interactivo | Client Component |
| `/checkout` | Pago con Stripe & mutación | Client Component + Server Action |
| `/orders` | Historial de órdenes de compra | Server Component + Suspense |

---

## 📑 Endpoints de la API Consumidos (Alineados a OpenAPI / Swagger)

- `GET /api/products` - Obtener lista de productos
- `GET /api/products/{id}` - Obtener detalle de un producto
- `POST /api/auth/login` - Autenticación y generación de cookie httpOnly
- `POST /api/auth/register` - Registro de nuevos usuarios
- `GET /api/orders` - Obtener historial de órdenes
- `POST /api/orders` - Crear orden y procesar pago en Stripe
