<?php

namespace Give\Framework\PaymentGateways\Commands;

use Give\Helpers\Form\Utils;

/**
 * This class uses to get redirect for failed offsite payment.
 *
 * @unreleased
 */
class RedirectOffsiteFailedPayment extends RedirectPaymentCommand
{

    /**
     * @inheritDoc
     */
    public function getUrl($donationFormPageUrl = null)
    {
        $formId = give_get_payment_form_id($this->donationId);
        $isEmbedDonationForm = ! Utils::isLegacyForm($formId);

        $url = $isEmbedDonationForm ?
            Utils::createFailedPageURL($donationFormPageUrl ?: get_permalink($formId)) :

            // Note: do not use give_success_page_url filter hook (inside give_get_success_page_url function)
            // to alter redirect url. We recommand to use give_gateway_payment_success_redirect_url filter hook.
            // We are using this function to get success page url for backward compatibility for legacy form templat.e
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
}


