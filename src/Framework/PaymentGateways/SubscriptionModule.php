<?php

namespace Give\Framework\PaymentGateways;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Contracts\SubscriptionModuleInterface;
use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
abstract class SubscriptionModule implements SubscriptionModuleInterface
{
    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function canEditPaymentGatewaySubscription(Subscription $subscriptionModel)
    {
        return $subscriptionModel->gatewaySubscriptionId &&
            in_array($subscriptionModel->status->getValue(), ['active', 'failing'], true);
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function canUpdatePaymentGatewaySubscriptionAmount(Subscription $subscriptionModel)
    {
        return $this->canEditPaymentGatewaySubscription($subscriptionModel) &&
            method_exists($this, 'updateSubscriptionAmount');
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function canUpdatePaymentGatewaySubscriptionPaymentMethod(Subscription $subscriptionModel)
    {
        return $this->canEditPaymentGatewaySubscription($subscriptionModel) &&
            method_exists(
                $this,
                'updateSubscriptionPaymentMethod'
            );
    }

    /**
     * Return flag whether subscription synchronizable.
     *
     * @unreleased
     *
     * @param Subscription $subscriptionModel
     *
     * @return bool
     */
    public function canSyncSubscriptionWithPaymentGateway(Subscription $subscriptionModel)
    {
        return method_exists(
            $this,
            'getSubscriptionTransactionsFromPaymentGateway'
        );
    }

    /**
     * Return flag whether subscription payment refundable.
     *
     * @unreleased
     *
     * @param Subscription $subscriptionModel
     * @param Donation $donationModel
     *
     * @return bool
     */
    public function canRefundPaymentGatewaySubscriptionPayment(Subscription $subscriptionModel, Donation $donationModel)
    {
        return $this->canEditPaymentGatewaySubscription($subscriptionModel) &&
            method_exists(
            $this,
            'refundSubscriptionPayment'
        );
    }
}
