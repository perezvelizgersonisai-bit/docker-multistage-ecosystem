<?php

namespace App\Services\Payments;

use Stripe\PaymentIntent;
use Stripe\StripeClient;

/**
 * Implementacion real de PaymentGatewayInterface usando el SDK oficial
 * de Stripe (stripe/stripe-php) mediante PaymentIntents.
 */
class StripePaymentGateway implements PaymentGatewayInterface
{
    protected StripeClient $client;

    public function __construct()
    {
        $this->client = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * @return array{id: string, status: string, amount: float, currency: string}
     */
    public function charge(float $amount, string $currency, string $paymentMethodId, string $description = ''): array
    {
        /** @var PaymentIntent $intent */
        $intent = $this->client->paymentIntents->create([
            // Stripe trabaja montos en la unidad minima de la moneda (centavos).
            'amount' => (int) round($amount * 100),
            'currency' => $currency,
            'payment_method' => $paymentMethodId,
            'description' => $description,
            'confirm' => true,
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
        ]);

        return [
            'id' => $intent->id,
            'status' => $intent->status,
            'amount' => $amount,
            'currency' => $currency,
        ];
    }
}
