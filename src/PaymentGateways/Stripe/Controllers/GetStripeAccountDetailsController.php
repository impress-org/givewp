<?php

namespace Give\PaymentGateways\Stripe\Controllers;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\PaymentGateways\Stripe\DataTransferObjects\GetStripeAccountDetailsDto;
use Give\PaymentGateways\Stripe\Repositories\AccountDetail;

/**
 * Class GetStripeAccountDetailsController
 * @package Give\PaymentGateways\Stripe\Controllers
 *
 * @since 2.13.0
 */
class GetStripeAccountDetailsController
{
    /**
     * @var AccountDetail
     */
    private $accountDetailServiceProvider;

    /**
     * @since 2.13.3
     *
     * @param AccountDetail $accountDetailServiceProvider
     */
    public function __construct(AccountDetail $accountDetailServiceProvider)
    {
        $this->accountDetailServiceProvider = $accountDetailServiceProvider;
    }

    /**
     * @since 2.13.3
     */
    public function __invoke()
    {
        $this->validateRequest();
        $requestedData = GetStripeAccountDetailsDto::fromArray(give_clean($_POST));

        try {
            wp_send_json_success(
                $this->accountDetailServiceProvider
                    ->getAccountDetailBySlug($requestedData->accountSlug)
                    ->toArray()
            );
        } catch (InvalidArgumentException $e) {
            wp_send_json_error();
        }
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
}
