<?php

namespace Give\Framework\PaymentGateways\Types;

use Give\Framework\PaymentGateways\Contracts\OffsiteGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\Traits\OffsiteGateway;

/**
 * @unreleased
 */
abstract class OffSitePaymentGateway extends PaymentGateway implements OffsiteGatewayInterface
{
    use OffsiteGateway;
}