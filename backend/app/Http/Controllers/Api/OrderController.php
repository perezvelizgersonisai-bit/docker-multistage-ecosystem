<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Creacion de ordenes de compra, historial de compras del usuario
 * autenticado, y procesamiento de pago con Stripe.
 * Todas las rutas de este controlador requieren un token JWT valido.
 */
class OrderController extends Controller
{
    public function __construct(
        protected PaymentGatewayInterface $paymentGateway
    ) {}

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Órdenes"},
     *     summary="Consultar el historial de compras del usuario autenticado",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de órdenes del usuario"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(): JsonResponse
    {
        $orders = Auth::guard('api')->user()
            ->orders()
            ->with(['items.product', 'payment'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     tags={"Órdenes"},
     *     summary="Ver el detalle de una orden propia",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalle de la orden"),
     *     @OA\Response(response=404, description="Orden no encontrada")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->findOwnOrder($id);

        if (! $order) {
            return $this->orderNotFound();
        }

        $order->load(['items.product', 'payment']);

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Órdenes"},
     *     summary="Crear una nueva orden de compra a partir de una lista de productos",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items"},
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Orden creada en estado 'pending'"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Error de validación o stock insuficiente")
     * )
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $items = $request->validated('items');

        try {
            $order = DB::transaction(function () use ($items) {
                $order = Order::query()->create([
                    'user_id' => Auth::guard('api')->id(),
                    'status' => Order::STATUS_PENDING,
                    'total' => 0,
                ]);

                $total = 0;

                foreach ($items as $item) {
                    // lockForUpdate evita condiciones de carrera sobre el stock
                    // si dos ordenes se procesan al mismo tiempo.
                    $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);

                    if ($product->stock < $item['quantity']) {
                        throw new \RuntimeException("Stock insuficiente para el producto \"{$product->name}\".");
                    }

                    $subtotal = $product->price * $item['quantity'];
                    $total += $subtotal;

                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price,
                        'subtotal' => $subtotal,
                    ]);

                    $product->decrement('stock', $item['quantity']);
                }

                $order->update(['total' => $total]);

                return $order;
            });
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $order->load(['items.product']);

        return response()->json([
            'success' => true,
            'message' => 'Orden creada correctamente. Procede al pago para confirmarla.',
            'data' => new OrderResource($order),
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{id}/pay",
     *     tags={"Órdenes"},
     *     summary="Procesar el pago de una orden pendiente mediante Stripe",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_method"},
     *             @OA\Property(property="payment_method", type="string", example="pm_card_visa", description="Id de PaymentMethod de Stripe (usa pm_card_visa en modo prueba)")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Pago procesado, orden marcada como pagada"),
     *     @OA\Response(response=402, description="El pago fue rechazado por Stripe"),
     *     @OA\Response(response=404, description="Orden no encontrada"),
     *     @OA\Response(response=409, description="La orden ya fue pagada o cancelada")
     * )
     */
    public function pay(PayOrderRequest $request, int $id): JsonResponse
    {
        $order = $this->findOwnOrder($id);

        if (! $order) {
            return $this->orderNotFound();
        }

        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Esta orden ya no está pendiente de pago (estado actual: '.$order->status.').',
            ], 409);
        }

        $charge = $this->paymentGateway->charge(
            amount: (float) $order->total,
            currency: 'usd',
            paymentMethodId: $request->validated('payment_method'),
            description: "Orden #{$order->id}"
        );

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'stripe_payment_intent_id' => $charge['id'],
            'amount' => $charge['amount'],
            'currency' => $charge['currency'],
            'status' => $charge['status'],
        ]);

        if (in_array($charge['status'], ['succeeded', 'processing'], true)) {
            $order->update(['status' => Order::STATUS_PAID]);
        }

        $order->load(['items.product', 'payment']);

        return response()->json([
            'success' => true,
            'message' => 'Pago procesado correctamente.',
            'data' => [
                'order' => new OrderResource($order),
                'payment_status' => $payment->status,
            ],
        ]);
    }

    /**
     * Busca una orden que pertenezca exclusivamente al usuario autenticado.
     */
    protected function findOwnOrder(int $id): ?Order
    {
        return Auth::guard('api')->user()
            ->orders()
            ->where('id', $id)
            ->first();
    }

    protected function orderNotFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'La orden solicitada no existe.',
        ], 404);
    }
}
