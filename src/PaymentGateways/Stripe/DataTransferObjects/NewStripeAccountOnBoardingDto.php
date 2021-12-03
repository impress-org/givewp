<?php

namespace Give\PaymentGateways\Stripe\DataTransferObjects;

/**
 * Class NewStripeAccountOnBoardingDto
 * @package Give\PaymentGateways\Stripe\DataTransferObjects
 *
 * @since 2.13.0
 */
class NewStripeAccountOnBoardingDto
{
    /**
     * @var mixed|string
     */
    public $stripePublishableKey;
    /**
     * @var mixed|string
     */
    public $stripeUserId;
    /**
     * @var mixed|string
     */
    public $stripeAccessToken;
    /**
     * @var mixed|string
     */
    public $stripeAccessTokenTest;
    /**
     * @var bool
     */
    public $isConnected;
    /**
     * @var mixed|string
     */
    public $stripePublishableKeyTest;
    /**
     * @var int
     */
    public $formId;

    /**
     * @since 2.13.0
     *
     * @param array $array
     *
     */
    public static function fromArray($array)
    {
        $self = new static();

        $self->formId = ! empty($array['post']) ? absint($array['post']) : 0;
        $self->stripePublishableKey = ! empty($array['stripe_publishable_key']) ? $array['stripe_publishable_key'] : '';
        $self->stripePublishableKeyTest = ! empty($array['stripe_publishable_key_test']) ? $array['stripe_publishable_key_test'] : '';
        $self->stripeUserId = ! empty($array['stripe_user_id']) ? $array['stripe_user_id'] : '';
        $self->stripeAccessToken = ! empty($array['stripe_access_token']) ? $array['stripe_access_token'] : '';
        $self->stripeAccessTokenTest = ! empty($array['stripe_access_token_test']) ? $array['stripe_access_token_test'] : '';
        $self->isConnected = ! empty($array['connected']) && absint($array['connected']);

        return $self;
    }

    /**
     * @since 2.13.0
     * @return bool
     */
    public function hasValidateData()
    {
        return $this->stripePublishableKey &&
               $this->stripeUserId &&
               $this->stripeAccessToken &&
               $this->stripeAccessTokenTest &&
               $this->isConnected;
    }
}
