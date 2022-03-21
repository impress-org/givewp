<?php

namespace Give\PaymentGateways\Gateways\Stripe\Traits\StripeApi;

use Exception;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\StripeApiRequestException;
use Stripe\Token as StripeToken;

/**
 * @unreleased
 */
trait RetrieveToken
{
    /**
     * This function will be used to fetch token details for given stripe token id.
     *
     * @unreleased
     *
     * @param array $args Additional arguments.
     * @param string $tokenId Stripe Token ID.
     *
     * @return array
     * @throws StripeApiRequestException
     */
    protected function getTokenDetails($tokenId, $args = [])
    {
        give_stripe_set_app_info();

        try {
            $requestArgs = wp_parse_args(
                $args,
                give_stripe_get_connected_account_options()
            );

            return StripeToken::retrieve($tokenId, $requestArgs)
                ->toArray();
        } catch (Exception $e) {
            throw new StripeApiRequestException(
                sprintf(
                /* translators: 1: Exception Message Body */
                    esc_html__('Unable to retrieve token. Details: %1$s', 'give'),
                    $e->getMessage()
                )
            );
        }
    }
}
