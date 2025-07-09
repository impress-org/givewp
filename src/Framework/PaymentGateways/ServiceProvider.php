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
 * @since 4.5.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @since 4.5.0
     */
    public function register()
    {
        // TODO: Implement register() method.
    }

    /**
     * @since 4.5.0
     */
    public function boot()
    {
        $this->registerWebhookEventHandlers();
    }

    /**
     * @since 4.5.0
     */
    private function registerWebhookEventHandlers()
    {
        add_action('init', function () {
            $registeredPaymentGatewayIds = give()->gateways->getPaymentGateways();
            foreach ($registeredPaymentGatewayIds as $gatewayId => $class) {
                $this->addDonationStatusEventHandlers($gatewayId);
                $this->addSubscriptionStatusEventHandlers($gatewayId);
                $this->addSubscriptionFirstDonationEventHandler($gatewayId);
                $this->addSubscriptionRenewalDonationEventHandler($gatewayId);
            }
        }, 999);
    }

    /**
     * @since 4.5.0
     */
    private function addDonationStatusEventHandlers(string $gatewayId)
    {
        foreach (DonationStatus::values() as $status) {
            if ($eventHandlerClass = (new GetEventHandlerClassByDonationStatus())($status)) {
                Hooks::addAction(
                    sprintf('givewp_%s_webhook_event_donation_status_%s', $gatewayId, $status->getValue()),
                    $eventHandlerClass, '__invoke', 10, 3
                );
            }
        }
    }

    /**
     * @since 4.5.0
     */
    private function addSubscriptionStatusEventHandlers(string $gatewayId)
    {
        foreach (SubscriptionStatus::values() as $status) {
            if ($eventHandlerClass = (new GetEventHandlerClassBySubscriptionStatus())($status)) {
                $parameterCount = $status->isActive() ? 3 : 2;
                Hooks::addAction(
                    sprintf('givewp_%s_webhook_event_subscription_status_%s', $gatewayId, $status->getValue()),
                    $eventHandlerClass, '__invoke', 10, $parameterCount
                );
            }
        }
    }

    /**
     * @since 4.5.0
     */
    private function addSubscriptionFirstDonationEventHandler(string $gatewayId)
    {
        Hooks::addAction(
            sprintf('givewp_%s_webhook_event_subscription_first_donation', $gatewayId),
            SubscriptionFirstDonationCompleted::class, '__invoke', 10, 5
        );
    }

    /**
     * @since 4.5.0
     */
    private function addSubscriptionRenewalDonationEventHandler(string $gatewayId)
    {
        Hooks::addAction(
            sprintf('givewp_%s_webhook_event_subscription_renewal_donation', $gatewayId),
            SubscriptionRenewalDonationCreated::class, '__invoke', 10, 2
        );
    }
}
