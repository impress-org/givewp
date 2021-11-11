<?php

namespace Give\PaymentGateways\Gateways\TestGateway\Actions;

use Give\Framework\Http\Response\Types\RedirectResponse;

/**
 * Class PublishPaymentAndSendToSuccessPage
 * @unreleased
 */
class PublishPaymentAndSendToSuccessPage
{
    /**
     * @unreleased
     *
     * @return RedirectResponse
     */
    public function __invoke($donationId)
    {
        give_update_payment_status($donationId, 'publish');

        return new RedirectResponse(give_get_success_page_uri());
    }
}
