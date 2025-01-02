<?php

namespace Give\Framework\PaymentGateways;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationAbandoned;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationCancelled;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationCompleted;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationFailed;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationPending;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationPreapproval;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationProcessing;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationRefunded;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationRevoked;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionActive;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionCancelled;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionCompleted;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionExpired;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionFailing;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionFirstDonationCompleted;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionRenewalDonationCreated;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionSuspended;
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
                $this->registerDonationEventHandlers($gatewayId);
                $this->registerSubscriptionEventHandlers($gatewayId);
            }
        }, 999);
    }

    /**
     * @unreleased
     */
    private function registerDonationEventHandlers(string $gatewayId)
    {
        foreach (DonationStatus::values() as $status) {
            switch ($status) {
                case $status->isAbandoned():
                    $this->addDonationStatusEventHandler($gatewayId, $status, DonationAbandoned::class);
                    break;
                case $status->isCancelled():
                    $this->addDonationStatusEventHandler($gatewayId, $status, DonationCancelled::class);
                    break;
                case $status->isComplete():
                    $this->addDonationStatusEventHandler($gatewayId, $status, DonationCompleted::class);
                    break;
                case $status->isFailed():
                    $this->addDonationStatusEventHandler($gatewayId, $status, DonationFailed::class);
                    break;
                case $status->isPending():
                    $this->addDonationStatusEventHandler($gatewayId, $status, DonationPending::class);
                    break;
                case $status->isPreapproval():
                    $this->addDonationStatusEventHandler($gatewayId, $status, DonationPreapproval::class);
                    break;
                case $status->isProcessing():
                    $this->addDonationStatusEventHandler($gatewayId, $status, DonationProcessing::class);
                    break;
                case $status->isRefunded():
                    $this->addDonationStatusEventHandler($gatewayId, $status, DonationRefunded::class);
                    break;
                case $status->isRevoked():
                    $this->addDonationStatusEventHandler($gatewayId, $status, DonationRevoked::class);
                    break;
            }
        }
    }

    /**
     * @unreleased
     */
    private function addDonationStatusEventHandler(
        string $gatewayId,
        DonationStatus $status,
        string $eventHandlerClass
    ) {
        Hooks::addAction(
            sprintf(
                'givewp_%s_webhook_event_donation_status_%s',
                $gatewayId,
                $status->getValue()
            ),
            $eventHandlerClass
        );
    }

    /**
     * @unreleased
     */
    private function registerSubscriptionEventHandlers(string $gatewayId)
    {
        $this->addSubscriptionFirstDonationEventHandler($gatewayId);
        $this->addSubscriptionRenewalDonationEventHandler($gatewayId);

        foreach (SubscriptionStatus::values() as $status) {
            switch ($status) {
                case $status->isActive():
                    $this->addSubscriptionStatusEventHandler($gatewayId, $status, SubscriptionActive::class);
                    break;
                case $status->isCancelled():
                    $this->addSubscriptionStatusEventHandler($gatewayId, $status, SubscriptionCancelled::class);
                    break;
                case $status->isCompleted():
                    $this->addSubscriptionStatusEventHandler($gatewayId, $status, SubscriptionCompleted::class);
                    break;
                case $status->isExpired():
                    $this->addSubscriptionStatusEventHandler($gatewayId, $status, SubscriptionExpired::class);
                    break;
                case $status->isFailing():
                    $this->addSubscriptionStatusEventHandler($gatewayId, $status, SubscriptionFailing::class);
                    break;
                case $status->isSuspended():
                    $this->addSubscriptionStatusEventHandler($gatewayId, $status, SubscriptionSuspended::class);
                    break;
            }
        }
    }

    /**
     * @unreleased
     */
    private function addSubscriptionFirstDonationEventHandler(string $gatewayId)
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
    private function addSubscriptionRenewalDonationEventHandler(string $gatewayId)
    {
        Hooks::addAction(
            sprintf(
                'givewp_%s_webhook_event_subscription_renewal_donation',
                $gatewayId
            ),
            SubscriptionRenewalDonationCreated::class
        );
    }

    /**
     * @unreleased
     */
    private function addSubscriptionStatusEventHandler(
        string $gatewayId,
        SubscriptionStatus $status,
        string $eventHandlerClass
    ) {
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
