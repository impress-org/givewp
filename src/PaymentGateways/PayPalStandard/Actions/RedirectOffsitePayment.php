<?php

namespace Give\PaymentGateways\PayPalStandard\Actions;

use Give\Helpers\Form\Utils;
use Give\Session\SessionDonation\DonationAccessor;

/**
 * This class handles after offsite payment redirect.
 *
 * @unreleased
 */
class RedirectOffsitePayment
{
    private $donationId;

    /**
     * @unreleased
     * @param $donationId
     */
    public function __construct( $donationId )
    {
        $this->donationId = $donationId;
    }

    /**
     * @unreleased
     *
     * @return string
     */
    public function getReceiptPageUrl()
    {
        $formId = give_get_payment_form_id($this->donationId);
        $isEmbedDonationForm = ! Utils::isLegacyForm($formId);

        return $isEmbedDonationForm ?
            Utils::createSuccessPageURL($this->getDonationFormPageUrl() ?: get_permalink($formId)) :
            give_get_success_page_url();
    }

    /**
     * @unreleased
     *
     * @return string
     */
    public function getFailedPageUrl()
    {
        $formId = give_get_payment_form_id($this->donationId);
        $isEmbedDonationForm = ! Utils::isLegacyForm($formId);

        return $isEmbedDonationForm ?
            Utils::createFailedPageURL($this->getDonationFormPageUrl() ?: get_permalink($formId)) :
            give_get_failed_transaction_uri();
    }

    /**
     * @unreleased
     *
     * @return string
     */
    protected function getDonationFormPageUrl()
    {
        return (new DonationAccessor())->get()->formEntry->currentUrl;
    }
}
