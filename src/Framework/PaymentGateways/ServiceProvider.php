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
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

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
        $registeredPaymentGatewayIds = give()->gateways->getPaymentGateways();
        foreach ($registeredPaymentGatewayIds as $gatewayId) {
            $this->registerDonationEventHandlers($gatewayId);
        }
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
                'givewp_%s_webhook_event_donation_%s',
                $gatewayId,
                $status->getValue()
            ),
            $eventHandlerClass
        );
    }
}
