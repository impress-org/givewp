<?php

namespace Give\PaymentGateways\Stripe\Controllers;

use Give\PaymentGateways\Stripe\DataTransferObjects\DisconnectStripeAccountDto;

/**
 * Class DisconnectStripeAccountController
 * @package Give\PaymentGateways\Stripe\Controllers
 *
 * @since 2.13.0
 */
class DisconnectStripeAccountController
{

    /**
     * @since 2.13.0
     */
    public function __invoke()
    {
        $this->validateRequest();

        $requestedData = DisconnectStripeAccountDto::fromArray(give_clean($_GET));

        $this->securityCheck($requestedData->accountSlug);

        give_stripe_disconnect_account($requestedData->accountSlug);

        wp_send_json_success();
    }

    /**
     * @since 2.13.0
     */
    private function validateRequest()
    {
        if ( ! current_user_can('manage_give_settings')) {
            die();
        }
    }

    /**
     * @since 2.13.0
     *
     * @param $accountSlug
     */
    private function securityCheck($accountSlug)
    {
        if ( ! check_admin_referer('give_disconnect_connected_stripe_account_' . $accountSlug)) {
            die();
        }
    }
}
