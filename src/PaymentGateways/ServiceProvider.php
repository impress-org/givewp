<?php

namespace Give\PaymentGateways;

use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\PaymentGateways\Routes\GatewayRoute;
use Give\Helpers\Hooks;
use Give\LegacyPaymentGateways\Actions\RegisterPaymentGatewaySettingsList;
use Give\PaymentGateways\Actions\RegisterPaymentGateways;
use Give\PaymentGateways\Gateways\Stripe\Helpers\CheckoutHelper;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * Class ServiceProvider - PaymentGateways
 *
 * The Service Provider for loading the Payment Gateways for Payment Flow 2.0
 *
 * @since 2.18.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton(PaymentGatewayRegister::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addFilter('give_register_gateway', RegisterPaymentGateways::class);
        Hooks::addFilter('give_payment_gateways', RegisterPaymentGatewaySettingsList::class);
        Hooks::addAction('template_redirect', GatewayRoute::class);

        /**
         * Stripe Checkout Redirect Handler
         */
        Hooks::addAction('wp_footer', CheckoutHelper::class, 'maybeHandleRedirect', 99999);
        Hooks::addAction('give_embed_footer', CheckoutHelper::class, 'maybeHandleRedirect', 99999);
    }
}
