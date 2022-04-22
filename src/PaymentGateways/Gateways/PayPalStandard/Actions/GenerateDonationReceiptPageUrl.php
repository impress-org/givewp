<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard\Actions;

use Give\Helpers\Form\Utils;
use Give\Session\SessionDonation\DonationAccessor;

/**
 * This class use to generate receipt page url.
 *
 * @since 2.19.0
 */
class GenerateDonationReceiptPageUrl
{

    /**
     * @since 2.19.0
     *
     * @return string
     */
    public function __invoke($donationId)
    {
        $formId = give_get_payment_form_id($donationId);
        $isEmbedDonationForm = ! Utils::isLegacyForm($formId);
        $donationFormPageUrl = (new DonationAccessor())->get()->formEntry->currentUrl ?: get_permalink($formId);

        return $isEmbedDonationForm ?
            Utils::createSuccessPageURL($donationFormPageUrl) :
            give_get_success_page_url();
    }
}
