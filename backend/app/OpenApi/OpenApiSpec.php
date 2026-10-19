<?php

namespace App\OpenApi;

/**
 * @OA\Info(
 *     title="E-commerce API",
 *     version="1.0.0",
 *     description="API RESTful para un e-commerce basico: registro y autenticacion de usuarios (JWT), catalogo de productos, ordenes de compra y procesamiento de pagos con Stripe.",
 *     @OA\Contact(email="soporte@ecommerce-api.test")
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Servidor de la API"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Autenticacion mediante token JWT. Formato: Bearer {token}"
 * )
 *
 * @OA\Tag(name="Autenticación", description="Registro, login, logout y perfil")
 * @OA\Tag(name="Productos", description="Catálogo de productos del e-commerce")
 * @OA\Tag(name="Órdenes", description="Creación de órdenes de compra y pago con Stripe")
 */
class OpenApiSpec
{
    // Esta clase solo existe como ancla para las anotaciones @OA de nivel superior.
}
