<?php

namespace App\Providers;

use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\StripePaymentGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Vincula el contrato de pagos con la implementacion de Stripe.
        // En pruebas automatizadas se puede sustituir por un fake (ver tests/Feature/OrderApiTest.php).
        $this->app->bind(PaymentGatewayInterface::class, StripePaymentGateway::class);
    }

    public function boot(): void
    {
        //
    }
}
