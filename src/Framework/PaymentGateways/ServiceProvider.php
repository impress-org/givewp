<?php

namespace Give\Framework\PaymentGateways;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\GetEventHandlerClassByDonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\GetEventHandlerClassBySubscriptionStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionFirstDonationCompleted;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionRenewalDonationCreated;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @unreleased
     */
    public function register()
    {
        // TODO: Implement register() method.
    }

    /**
     * @unreleased
     */
    public function boot()
    {
        add_action('give_init', function () {
            $registeredPaymentGatewayIds = give()->gateways->getPaymentGateways();
            foreach ($registeredPaymentGatewayIds as $gatewayId) {
                $this->registerDonationStatusEventHandlers($gatewayId);
                $this->registerSubscriptionStatusEventHandlers($gatewayId);
                $this->registerSubscriptionFirstDonationEventHandler($gatewayId);
                $this->registerSubscriptionRenewalDonationEventHandler($gatewayId);
            }
        }, 999);
    }

    /**
     * @unreleased
     */
    private function registerDonationStatusEventHandlers(string $gatewayId)
    {
        foreach (DonationStatus::values() as $status) {
            if ($eventHandlerClass = (new GetEventHandlerClassByDonationStatus())($status)) {
                Hooks::addAction(
                    sprintf(
                        'givewp_%s_webhook_event_donation_status_%s',
                        $gatewayId,
                        $status->getValue()
                    ),
                    $eventHandlerClass
                );
            }
        }
    }

    /**
     * @unreleased
     */
    private function registerSubscriptionStatusEventHandlers(string $gatewayId)
    {
        foreach (SubscriptionStatus::values() as $status) {
            if ($eventHandlerClass = (new GetEventHandlerClassBySubscriptionStatus())($status)) {
                Hooks::addAction(
                    sprintf(
                        'givewp_%s_webhook_event_subscription_status_%s',
                        $gatewayId,
                        $status->getValue()
                    ),
                    $eventHandlerClass
                );
            }
        }
    }

    /**
     * @unreleased
     */
    private function registerSubscriptionFirstDonationEventHandler(string $gatewayId)
    {
        Hooks::addAction(
            sprintf(
                'givewp_%s_webhook_event_subscription_first_donation',
                $gatewayId
            ),
            SubscriptionFirstDonationCompleted::class
        );
    }

    /**
     * @unreleased
     */
    private function registerSubscriptionRenewalDonationEventHandler(string $gatewayId)
    {
        Hooks::addAction(
            sprintf(
                'givewp_%s_webhook_event_subscription_renewal_donation',
                $gatewayId
            ),
            SubscriptionRenewalDonationCreated::class
        );
    }
}
