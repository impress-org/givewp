<?php

namespace Give\PaymentGateways\Gateways;

use Give\DonationForms\Models\DonationForm;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Exceptions\OverflowException;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\Support\Scripts\Concerns\HasScriptAssetFile;
use Give\Helpers\Hooks;
use Give\Log\Log;
use Give\PaymentGateways\Gateways\PayPalCommerce\PayPalCommerceGateway;
use Give\PaymentGateways\Gateways\PayPalCommerce\PayPalCommerceSubscriptionModule;
use Give\PaymentGateways\Gateways\Stripe\Actions\EnqueueStripeFormBuilderScripts;
use Give\PaymentGateways\Gateways\Stripe\Actions\UpdateStripeFormBuilderSettingsMeta;
use Give\PaymentGateways\Gateways\Stripe\LegacyStripeAdapter;
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
use Give\PaymentGateways\PayPalCommerce\PayPalCommerce;
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
                ]
            );
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
            if (!$registrar->hasPaymentGateway(TestGateway::id())) {
                $registrar->registerGateway(TestGateway::class);
            }

            if (!$registrar->hasPaymentGateway(TestOffsiteGateway::id())) {
                $registrar->registerGateway(TestOffsiteGateway::class);
            }

            $registrar->registerGateway(StripePaymentElementGateway::class);

            $registrar->unregisterGateway(PayPalCommerce::id());
            $registrar->registerGateway(PayPalCommerceGateway::class);
        });

        add_filter("givewp_create_payment_gateway_data_" . PayPalCommerce::id(), function ($gatewayData) {
            $gatewayData['payPalOrderId'] = $gatewayData['payPalOrderId'] ?? give_clean($_POST['payPalOrderId']);
            return $gatewayData;
        });

        add_filter('give_recurring_modify_donation_data', function($recurringData) {
            /**
             * PayPal Donations/Commerce (NextGen)
             * Optionally account for the period, frequency, and times values being passed via post data.
             */
            if(isset($_GET['action']) && 'give_paypal_commerce_create_plan_id' == $_GET['action']) {
                $recurringData['period'] = $recurringData['period'] ?: $recurringData['post_data']['period'];
                $recurringData['frequency'] = $recurringData['frequency'] ?: $recurringData['post_data']['frequency'];
                $recurringData['times'] = $recurringData['times'] ?: $recurringData['post_data']['times'];
            }

            return $recurringData;
        });

        /**
         * This module will eventually live in give-recurring
         */
        if (defined('GIVE_RECURRING_VERSION') && GIVE_RECURRING_VERSION) {
            add_filter(
                sprintf("givewp_gateway_%s_subscription_module", PayPalCommerce::id()),
                static function () {
                    return PayPalCommerceSubscriptionModule::class;
                }
            );
        }

        $this->addLegacyStripeAdapter();
        $this->addStripeWebhookListeners();
        $this->addStripeFormBuilderHooks();
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
        add_action('givewp_form_builder_updated', static function (DonationForm $form) {
            give(UpdateStripeFormBuilderSettingsMeta::class)->__invoke($form);
        });
    }
}
