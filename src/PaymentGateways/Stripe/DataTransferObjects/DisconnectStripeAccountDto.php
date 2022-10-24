<?php

namespace Give\PaymentGateways\Stripe\DataTransferObjects;

/**
 * Class DisconnectStripeAccountDto
 * @package Give\PaymentGateways\Stripe\DataTransferObjects
 *
 * @since   2.13.0
 */
final class DisconnectStripeAccountDto
{
    /**
     * @var array|string
     */
    public $accountType;
    /**
     * @var array|string
     */
    public $accountSlug;

    /**
     * @since 2.13.0
     *
     * @param $array
     *
     * @return self
     */
    public static function fromArray($array)
    {
        $self = new static();

        $self->accountType = ! empty($array['account_type']) ? $array['account_type'] : '';
        $self->accountSlug = ! empty($array['account_slug']) ? $array['account_slug'] : '';

        return $self;
    }
}
