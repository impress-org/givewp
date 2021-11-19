<?php

namespace Give\PaymentGateways\Stripe\DataTransferObjects;

/**
 * Class SetDefaultStripeAccountDto
 * @package Give\PaymentGateways\Stripe\DataTransferObjects
 *
 * @since 2.13.0
 */
class SetDefaultStripeAccountDto
{
    /**
     * @var mixed|string
     */
    public $accountSlug;

    /**
     * @var int|string
     */
    public $formId;

    /**
     * @since 2.13.0
     *
     * @param array $array
     */
    public static function fromArray($array)
    {
        $self = new static();

        $self->accountSlug = ! empty($array['account_slug']) ? $array['account_slug'] : '';
        $self->formId = ! empty($array['form_id']) ? absint($array['form_id']) : '';

        return $self;
    }
}
