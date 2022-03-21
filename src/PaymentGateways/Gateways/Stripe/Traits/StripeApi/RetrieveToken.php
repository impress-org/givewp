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
        // Set Application Info.
        give_stripe_set_app_info();

        try {
            $requestArgs = wp_parse_args(
                $args,
                give_stripe_get_connected_account_options()
            );

            // Retrieve Token Object.
            return StripeToken::retrieve($tokenId, $requestArgs)
                ->toArray();
        } catch (Exception $e) {
            throw new StripeApiRequestException(
                sprintf(
                /* translators: %s Exception Message Body */
                    __('Unable to retrieve token. Details: %s', 'give'),
                    $e->getMessage()
                )
            );
        }
    }
}
