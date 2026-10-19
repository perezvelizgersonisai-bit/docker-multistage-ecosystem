<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

/**
 * Catalogo de productos. Listar y ver un producto es publico;
 * crear, actualizar y eliminar requieren un token JWT valido
 * (ver el middleware "jwt.auth" en routes/api.php).
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Productos"},
     *     summary="Listar el catálogo público de productos",
     *     @OA\Response(response=200, description="Lista de productos")
     * )
     */
    public function index(): JsonResponse
    {
        $products = Product::query()->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     tags={"Productos"},
     *     summary="Ver el detalle de un producto",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalle del producto"),
     *     @OA\Response(response=404, description="Producto no encontrado")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::query()->find($id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'El producto solicitado no existe.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Productos"},
     *     summary="Registrar un nuevo producto en el catálogo",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","price","stock","sku"},
     *             @OA\Property(property="name", type="string", example="Mouse inalámbrico"),
     *             @OA\Property(property="description", type="string", example="Mouse óptico inalámbrico 2.4GHz"),
     *             @OA\Property(property="price", type="number", format="float", example=15.99),
     *             @OA\Property(property="stock", type="integer", example=50),
     *             @OA\Property(property="sku", type="string", example="MOU-0001")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Producto creado"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::query()->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Producto registrado correctamente en el catálogo.',
            'data' => new ProductResource($product),
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     tags={"Productos"},
     *     summary="Actualizar un producto existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Mouse inalámbrico Pro"),
     *             @OA\Property(property="price", type="number", format="float", example=19.99),
     *             @OA\Property(property="stock", type="integer", example=40)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Producto actualizado"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=404, description="Producto no encontrado")
     * )
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::query()->find($id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'El producto solicitado no existe.',
            ], 404);
        }

        $product->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado correctamente.',
            'data' => new ProductResource($product->fresh()),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     tags={"Productos"},
     *     summary="Eliminar un producto del catálogo",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Producto eliminado"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=404, description="Producto no encontrado")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::query()->find($id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'El producto solicitado no existe.',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado correctamente.',
        ]);
    }
}
