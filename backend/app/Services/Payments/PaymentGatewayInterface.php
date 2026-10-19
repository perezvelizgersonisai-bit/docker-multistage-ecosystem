<?php

namespace App\Services\Payments;

/**
 * Contrato de pasarela de pago. Desacopla la logica de negocio (OrderController)
 * del proveedor concreto (Stripe), facilitando pruebas automatizadas con un
 * "fake" y un eventual cambio de proveedor sin tocar los controladores.
 */
interface PaymentGatewayInterface
{
    /**
     * Cobra un monto usando un metodo de pago (token/id de Stripe) y devuelve
     * un arreglo normalizado con el resultado del cargo.
     *
     * @return array{id: string, status: string, amount: float, currency: string}
     */
    public function charge(float $amount, string $currency, string $paymentMethodId, string $description = ''): array;
}
