<?php

namespace Give\PaymentGateways\Gateways;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Exceptions\OverflowException;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\Support\Scripts\Concerns\HasScriptAssetFile;
use Give\Helpers\Hooks;
use Give\Log\Log;
use Give\PaymentGateways\Gateways\Offline\Actions\DisableGatewayWhenDisabledPerForm;
use Give\PaymentGateways\Gateways\Offline\Actions\EnqueueOfflineFormBuilderScripts;
use Give\PaymentGateways\Gateways\Offline\Actions\UpdateOfflineMetaFromFormBuilder;
use Give\PaymentGateways\Gateways\PayPalCommerce\PayPalCommerceGateway;
use Give\PaymentGateways\Gateways\Stripe\LegacyStripeAdapter;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Actions\AddStripeAttributesToNewForms;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Actions\EnqueueStripeFormBuilderScripts;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Actions\UpdateStripeFormBuilderSettingsMeta;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners\ChargeRefunded;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners\CustomerSubscriptionCreated;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners\CustomerSubscriptionDeleted;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners\InvoicePaymentFailed;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners\InvoicePaymentSucceeded;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners\PaymentIntentPaymentFailed;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners\PaymentIntentSucceeded;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\PaymentGateways\Gateways\TestOffsiteGateway\TestOffsiteGateway;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    use HasScriptAssetFile;

    /**
     * @since 3.0.0
     */
    public function register()
    {
        //
    }

    /**
     * @since 3.0.0
     */
    public function boot()
    {
        try {
            $this->registerGateways();
        } catch (Exception $e) {
            Log::error('Error Registering Gateways', [
                    'message' => $e->getMessage()
                ]);
        }
    }

    /**
     * @since 3.0.0
     *
     * @throws Exception
     * @throws OverflowException
     */
    private function registerGateways()
    {
        add_action('givewp_register_payment_gateway', static function (PaymentGatewayRegister $registrar) {
            // Enable as needed for testing but do not push to production
            // $registrar->registerGateway(TestOffsiteGateway::class);

            $registrar->registerGateway(StripePaymentElementGateway::class);

            $registrar->registerGateway(PayPalCommerceGateway::class);
        });

        $this->addLegacyStripeAdapter();
        $this->addStripeWebhookListeners();
        $this->addStripeFormBuilderHooks();
        $this->bootOfflineDonations();
    }

      /**
       * @since 3.0.0
     */
    private function addStripeWebhookListeners()
    {
        Hooks::addAction(
            'give_stripe_processing_payment_intent_succeeded',
            PaymentIntentSucceeded::class
        );

        Hooks::addAction(
            'give_stripe_processing_payment_intent_failed',
            PaymentIntentPaymentFailed::class
        );

        Hooks::addAction(
            'give_stripe_processing_charge_refunded',
            ChargeRefunded::class
        );

        Hooks::addAction(
            'give_recurring_stripe_processing_invoice_payment_succeeded',
            InvoicePaymentSucceeded::class
        );

        Hooks::addAction(
            'give_recurring_stripe_processing_invoice_payment_failed',
            InvoicePaymentFailed::class
        );

        Hooks::addAction(
            'give_recurring_stripe_processing_customer_subscription_created',
            CustomerSubscriptionCreated::class
        );

        Hooks::addAction(
            'give_recurring_stripe_processing_customer_subscription_deleted',
            CustomerSubscriptionDeleted::class
        );
    }

    /**
     * @since 3.0.0
     */
    private function addLegacyStripeAdapter()
    {
        /** @var LegacyStripeAdapter $legacyStripeAdapter */
        $legacyStripeAdapter = give(LegacyStripeAdapter::class);

        $legacyStripeAdapter->addDonationDetails();
        $legacyStripeAdapter->loadLegacyStripeWebhooksAndFilters();
    }

    /**
     * @since 3.0.0
     */
    private function addStripeFormBuilderHooks()
    {
        Hooks::addAction('givewp_form_builder_enqueue_scripts', EnqueueStripeFormBuilderScripts::class);
        Hooks::addAction('givewp_form_builder_new_form', AddStripeAttributesToNewForms::class);
        Hooks::addAction('givewp_form_builder_updated', UpdateStripeFormBuilderSettingsMeta::class);
    }

    private function bootOfflineDonations()
    {
        Hooks::addAction('givewp_form_builder_enqueue_scripts', EnqueueOfflineFormBuilderScripts::class);
        Hooks::addAction('givewp_form_builder_updated', UpdateOfflineMetaFromFormBuilder::class);
        Hooks::addFilter(
            'givewp_donation_form_enabled_gateways',
            DisableGatewayWhenDisabledPerForm::class,
            '__invoke',
            10,
            2
        );
    }
}
