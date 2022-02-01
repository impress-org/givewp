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
     * @param int $donationId
     *
     * @return void
     */
    public static function redirectToReceiptPage( $donationId ){
        $self = new static( $donationId );

        wp_safe_redirect( $self->getReceiptPageUrl( $self->getDonationFormPageUrl() ) );
        exit();
    }

    /**
     * @unreleased
     *
     * @param int $donationId
     *
     * @return void
     */
    public static function redirectToFailedPage( $donationId ){
        $self = new static( $donationId );

        wp_safe_redirect( $self->getFailedPageUrl( $self->getDonationFormPageUrl() ) );
        exit();
    }

    /**
     * @unreleased
     *
     * @param string $donationFormPageUrl
     *
     * @return string
     */
    protected function getReceiptPageUrl($donationFormPageUrl = null)
    {
        $formId = give_get_payment_form_id($this->donationId);
        $isEmbedDonationForm = ! Utils::isLegacyForm($formId);

        return $isEmbedDonationForm ?
            Utils::createSuccessPageURL($donationFormPageUrl ?: get_permalink($formId)) :
            give_get_success_page_url();
    }

    /**
     * @unreleased
     *
     * @param string $donationFormPageUrl
     *
     * @return mixed|void
     */
    protected function getFailedPageUrl($donationFormPageUrl = null)
    {
        $formId = give_get_payment_form_id($this->donationId);
        $isEmbedDonationForm = ! Utils::isLegacyForm($formId);

        $url = $isEmbedDonationForm ?
            Utils::createFailedPageURL($donationFormPageUrl ?: get_permalink($formId)) :
            give_get_failed_transaction_uri();

        /**
         * Filter the failed payment redirect url.
         *
         * @unreleased
         *
         * @param string $url
         * @param int $donationId
         */
        return apply_filters('give_gateway_payment_failed_redirect_url', $url, $this->donationId);
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
