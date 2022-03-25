<?php

namespace Give\PaymentGateways;

use Give\Framework\Migrations\MigrationsRegister;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\PaymentGateways\Routes\GatewayRoute;
use Give\Helpers\Hooks;
use Give\LegacyPaymentGateways\Actions\RegisterPaymentGatewaySettingsList;
use Give\PaymentGateways\Actions\RegisterPaymentGateways;
use Give\PaymentGateways\Gateways\PayPalStandard\Webhooks\WebhookRegister;
use Give\PaymentGateways\Gateways\Stripe\CheckoutGateway;
use Give\PaymentGateways\Gateways\Stripe\Controllers\UpdateStatementDescriptorAjaxRequestController;
use Give\PaymentGateways\Gateways\Stripe\Migrations\AddStatementDescriptorToStripeAccounts;
use Give\PaymentGateways\PayPalCommerce\Migrations\RemoveLogWithCardInfo;
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
        give()->singleton(WebhookRegister::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addFilter('give_register_gateway', RegisterPaymentGateways::class);
        Hooks::addFilter('give_payment_gateways', RegisterPaymentGatewaySettingsList::class);
        Hooks::addAction('template_redirect', GatewayRoute::class);
        Hooks::addAction(
            'wp_ajax_edit_stripe_account_statement_descriptor',
            UpdateStatementDescriptorAjaxRequestController::class
        );

        $this->registerMigrations();

        /**
         * Stripe Checkout Redirect Handler
         */
        Hooks::addAction('wp_footer', CheckoutGateway::class, 'maybeHandleRedirect', 99999);
        Hooks::addAction('give_embed_footer', CheckoutGateway::class, 'maybeHandleRedirect', 99999);
    }

    /**
     * @unreleased
     */
    private function registerMigrations()
    {
        $migrationRegisterer = give(MigrationsRegister::class);
        $migrations = [
            AddStatementDescriptorToStripeAccounts::class,
            RemoveLogWithCardInfo::class
        ];

        foreach ($migrations as $migration) {
            $migrationRegisterer->addMigration($migration);
        }
    }
}
