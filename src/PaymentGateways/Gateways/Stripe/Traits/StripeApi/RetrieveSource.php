<?php

namespace Give\PaymentGateways\Gateways\Stripe\Traits\StripeApi;

use Exception;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\StripeApiRequestException;
use Stripe\Source;

/**
 * @unreleased
 */
trait RetrieveSource
{
    /**
     * @unreleased
     * @return Source
     * @throws StripeApiRequestException
     */
    public function getSourceDetails($sourceId, $options = [])
    {
        // Set Application Info.
        give_stripe_set_app_info();

        try {
            // Retrieve Source Object.
            return Source::retrieve(
                $sourceId,
                wp_parse_args(
                    $options,
                    give_stripe_get_connected_account_options()
                )
            );
        } catch (Exception $e) {
            // Something went wrong outside of Stripe.
            throw new StripeApiRequestException(
                sprintf(
                /* translators: %s Exception Message Body */
                    esc_html__('Unable to retrieve source. Details: %s', 'give'),
                    $e->getMessage()
                )
            );
        }
    }
}
