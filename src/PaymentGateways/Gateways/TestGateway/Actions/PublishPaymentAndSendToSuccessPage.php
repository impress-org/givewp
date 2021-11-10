<?php

namespace Give\PaymentGateways\Gateways\TestGateway\Actions;

/**
 * Class PublishPaymentAndSendToSuccessPage
 * @unreleased
 */
class PublishPaymentAndSendToSuccessPage
{
    /**
     * @unreleased
     *
     * @return void
     */
    public function __invoke($donationId)
    {
        give_update_payment_status($donationId, 'publish');
        give_send_to_success_page();
    }
}
