<?php

namespace Give\Framework\PaymentGateways;

use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionAmountEditable;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionPaymentMethodEditable;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionTransactionsSynchronizable;
use Give\Framework\PaymentGateways\Contracts\SubscriptionModuleInterface;

/**
 * @unreleased
 *
 * @template G
 */
abstract class SubscriptionModule implements SubscriptionModuleInterface
{
    /**
     * @var G
     */
    protected $gateway;

    /**
     * @param G $gateway
     */
    public function setGateway(PaymentGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @inheritDoc
     */
    public function canSyncSubscriptionWithPaymentGateway()
    {
        return $this instanceof SubscriptionTransactionsSynchronizable;
    }

    /**
     * @inheritDoc
     */
    public function canUpdateSubscriptionAmount()
    {
        return $this instanceof SubscriptionAmountEditable;
    }

    /**
     * @inheritDoc
     */
    public function canUpdateSubscriptionPaymentMethod()
    {
        return $this instanceof SubscriptionPaymentMethodEditable;
    }
}
