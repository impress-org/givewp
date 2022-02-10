<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Actions;

use Give\Helpers\Form\Utils;
use Give\Session\SessionDonation\DonationAccessor;

/**
 * This class use to generate failed page url.
 *
 * @unreleased
 */
class GenerateDonationFailedPageUrl
{
    /**
     * @unreleased
     *
     * @return string
     */
    public function __invoke($donationId)
    {
        $formId = give_get_payment_form_id($donationId);
        $isEmbedDonationForm = ! Utils::isLegacyForm($formId);
        $donationFormPageUrl = (new DonationAccessor())->get()->formEntry->currentUrl ?: get_permalink($formId);

        return $isEmbedDonationForm ?
            Utils::createFailedPageURL($donationFormPageUrl) :
            give_get_failed_transaction_uri();
    }
}
