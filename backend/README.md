# E-commerce API — Laravel 12, JWT, Stripe y Swagger/OpenAPI

API RESTful segura para un e-commerce básico, desarrollada con **Laravel 12**. Permite gestionar un catálogo de productos, registrar clientes, crear órdenes de compra y procesarlas con **Stripe**, todo documentado automáticamente con **Swagger/OpenAPI**.

## Tabla de contenido

- [Arquitectura](#arquitectura)
- [Estructura de carpetas](#estructura-de-carpetas)
- [Requisitos previos](#requisitos-previos)
- [Instrucciones de instalación](#instrucciones-de-instalación)
- [Documentación interactiva (Swagger UI)](#documentación-interactiva-swagger-ui)
- [Documentación de endpoints](#documentación-de-endpoints)
- [Flujo completo de compra (ejemplos con curl)](#flujo-completo-de-compra-ejemplos-con-curl)
- [Pruebas automatizadas](#pruebas-automatizadas)

## Arquitectura

- **Autenticación**: JWT stateless mediante `tymon/jwt-auth` (guard `api`, driver `jwt`). Contraseñas cifradas con `bcrypt`.
- **Pagos desacoplados**: el controlador de órdenes depende de la interfaz `App\Services\Payments\PaymentGatewayInterface`, no directamente de Stripe. La implementación real (`StripePaymentGateway`) se inyecta en `AppServiceProvider`. Esto permite sustituir la pasarela por un *fake* en las pruebas automatizadas sin tocar el controlador, y facilitaría cambiar de proveedor de pago en el futuro.
- **Transacciones de base de datos**: la creación de una orden (descuento de stock + registro de `order_items`) ocurre dentro de una transacción con bloqueo de fila (`lockForUpdate`), evitando condiciones de carrera si dos compras del mismo producto llegan al mismo tiempo.
- **Documentación autogenerada**: los endpoints se documentan con anotaciones `@OA\...` (`darkaonline/l5-swagger`, basado en `zircote/swagger-php`) directamente en los controladores, y se sirven en una interfaz Swagger UI interactiva.

## Estructura de carpetas

```
app/
├── Http/
│   ├── Controllers/Api/{AuthController, ProductController, OrderController}.php
│   ├── Requests/{RegisterRequest, LoginRequest, StoreProductRequest, UpdateProductRequest, StoreOrderRequest, PayOrderRequest}.php
│   └── Resources/{UserResource, ProductResource, OrderResource, OrderItemResource, PaymentResource}.php
├── Models/{User, Product, Order, OrderItem, Payment}.php
├── Services/Payments/{PaymentGatewayInterface, StripePaymentGateway}.php
└── OpenApi/OpenApiSpec.php   (anotaciones @OA\Info de nivel superior)

config/{auth.php, jwt.php, l5-swagger.php, services.php}

database/
├── migrations/  (users, cache, jobs, products, orders, order_items, payments)
└── seeders/{UserSeeder, ProductSeeder}.php

routes/api.php
tests/Feature/{AuthTest, ProductApiTest, OrderApiTest}.php
```

## Requisitos previos

- PHP >= 8.2
- Composer 2
- Servidor MySQL (esta actividad pide explícitamente MySQL)
- Una cuenta de Stripe en **modo de prueba** (gratis) — https://dashboard.stripe.com/register

## Instrucciones de instalación

```bash
# 1. Clonar el repositorio
git clone <URL-del-repositorio>
cd ecommerce-api

# 2. Instalar dependencias de PHP
composer install

# 3. Crear el archivo de entorno
cp .env.example .env

# 4. Generar la clave de la aplicación
php artisan key:generate

# 5. Generar el secreto JWT
php artisan jwt:secret

# 6. Crear la base de datos en MySQL (una vez, desde tu cliente MySQL)
#    CREATE DATABASE ecommerce_api CHARACTER SET utf8mb4;
#    Luego edita en .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD según tu instalación local.

# 7. Configurar las llaves de Stripe en .env (modo de prueba):
#    STRIPE_KEY=pk_test_...
#    STRIPE_SECRET=sk_test_...
#    Se obtienen en: https://dashboard.stripe.com/test/apikeys

# 8. Ejecutar migraciones y cargar datos de ejemplo
php artisan migrate --seed

# 9. Generar la documentación Swagger
php artisan l5-swagger:generate

# 10. Levantar el servidor de desarrollo
php artisan serve
```

La API queda disponible en `http://localhost:8000/api`.

> El seeder crea un cliente de prueba: **email:** `cliente@tienda.com` — **password:** `password123`, y 6 productos de ejemplo.

## Documentación interactiva (Swagger UI)

Con el servidor corriendo, abre en el navegador:

```
http://localhost:8000/api/documentation
```

Ahí puedes ver todos los endpoints documentados, sus parámetros, cuerpos de petición y respuestas esperadas, y probarlos directamente desde el navegador (botón **Authorize** para pegar tu token JWT como `Bearer {token}`).

## Documentación de endpoints

### Autenticación

| Método | Endpoint | Auth | Descripción |
|---|---|---|---|
| `POST` | `/api/auth/register` | No | Registrar un nuevo cliente |
| `POST` | `/api/auth/login` | No | Iniciar sesión y obtener token JWT |
| `POST` | `/api/auth/logout` | Sí | Cerrar sesión e invalidar token |
| `GET` | `/api/auth/me` | Sí | Perfil del usuario autenticado |

### Productos

| Método | Endpoint | Auth | Descripción |
|---|---|---|---|
| `GET` | `/api/products` | No | Listado público del catálogo |
| `GET` | `/api/products/{id}` | No | Detalle de un producto |
| `POST` | `/api/products` | Sí | Crear un producto |
| `PUT`/`PATCH` | `/api/products/{id}` | Sí | Actualizar un producto |
| `DELETE` | `/api/products/{id}` | Sí | Eliminar un producto |

### Órdenes

| Método | Endpoint | Auth | Descripción |
|---|---|---|---|
| `POST` | `/api/orders` | Sí | Crear una orden (`status: pending`) a partir de una lista de productos y cantidades |
| `GET` | `/api/orders` | Sí | Historial de compras del usuario autenticado |
| `GET` | `/api/orders/{id}` | Sí | Detalle de una orden propia |
| `POST` | `/api/orders/{id}/pay` | Sí | Procesar el pago de una orden pendiente mediante Stripe |

## Flujo completo de compra (ejemplos con curl)

### 1. Registrar / iniciar sesión

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "cliente@tienda.com", "password": "password123"}'
```

Guarda el `token` de la respuesta.

### 2. Consultar el catálogo (público, no requiere token)

```bash
curl -X GET http://localhost:8000/api/products
```

### 3. Crear una orden con uno o más productos

```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{
        "items": [
          { "product_id": 1, "quantity": 2 },
          { "product_id": 2, "quantity": 1 }
        ]
      }'
```

La respuesta incluye el `id` de la orden creada, con `status: "pending"` y el `total` calculado. El stock de los productos se descuenta de inmediato.

### 4. Pagar la orden con Stripe (modo de prueba)

En modo de prueba, Stripe ofrece métodos de pago predefinidos como `pm_card_visa` (tarjeta que siempre aprueba) o `pm_card_visa_chargeDeclined` (tarjeta que siempre rechaza), útiles para probar sin una tarjeta real:

```bash
curl -X POST http://localhost:8000/api/orders/1/pay \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{ "payment_method": "pm_card_visa" }'
```

Si el pago es exitoso, la orden cambia a `status: "paid"` y se crea un registro en `payments` con el `stripe_payment_intent_id`.

### 5. Consultar el historial de compras

```bash
curl -X GET http://localhost:8000/api/orders \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

## Pruebas automatizadas

Las pruebas de `OrderApiTest` sustituyen la pasarela de Stripe por un *fake* interno (ver `PaymentGatewayInterface`), por lo que **no requieren conexión a internet ni llaves reales de Stripe** para ejecutarse:

```bash
php artisan test
```

Cobertura: registro/login, catálogo público vs. protegido, creación de órdenes con descuento de stock, validación de stock insuficiente, pago exitoso, rechazo de pago duplicado, y aislamiento de órdenes entre usuarios distintos.
