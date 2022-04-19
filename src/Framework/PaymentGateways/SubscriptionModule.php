<?php

namespace Give\Framework\PaymentGateways;

use Give\Framework\PaymentGateways\Contracts\SubscriptionModuleInterface;
use Give\Framework\PaymentGateways\Traits\HasRouteMethods;

/**
 * @unreleased
 */
abstract class SubscriptionModule implements SubscriptionModuleInterface
{
    use HasRouteMethods;
}
