<?php

namespace Give\Helpers\Frontend;

use Give\Helpers\Form\Utils as FormUtils;
use Give\Session\SessionDonation\DonationAccessor;

/**
 * Class ConfirmDonation
 *
 * @package Give\Helpers\Frontend
 */
class ConfirmDonation
{
    /**
     * Store posted data to donation session to access it in iframe if we are on payment confirmation page.
     * This function will return true if data stored successfully in purchase session (session key name "give_purchase" ) otherwise false.
     *
     * Note: only for internal use.
     *
     * @since 2.7.0
     * @return bool
     */
    public static function storePostedDataInDonationSession()
    {
        $isShowingDonationReceipt = ! empty($_REQUEST['giveDonationAction']) && 'showReceipt' === give_clean( // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- donation confirmation page that an off-site gateway sends the donor back to; a WordPress nonce cannot be sent here.
                $_REQUEST['giveDonationAction'] // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- donation confirmation page that an off-site gateway sends the donor back to; a WordPress nonce cannot be sent here. give_clean() unslashes and sanitizes with sanitize_text_field(), and drops serialized data.
            );

        if ( ! $isShowingDonationReceipt || ! isset($_GET['payment-confirmation'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- donation confirmation page that an off-site gateway sends the donor back to; a WordPress nonce cannot be sent here.
            return false;
        }

        $paymentGatewayId = ucfirst(give_clean($_GET['payment-confirmation'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- donation confirmation page that an off-site gateway sends the donor back to; a WordPress nonce cannot be sent here. give_clean() unslashes and sanitizes with sanitize_text_field(), and drops serialized data.

        $session = new DonationAccessor();
        $session->store("postDataFor{$paymentGatewayId}", array_map('give_clean', $_POST)); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- donation confirmation page that an off-site gateway sends the donor back to; a WordPress nonce cannot be sent here. The posted data is sanitized with give_clean() and only kept in the donation session.

        return true;
    }

    /**
     * Remove posted data from donation session just before rendering payment confirmation view because beyond this view this data is not useful.
     *
     * Note: Only for internal use.
     *
     * @since 2.7.0
     */
    public static function removePostedDataFromDonationSession()
    {
        $paymentGatewayId = ucfirst(give_clean($_GET['payment-confirmation'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- donation confirmation page that an off-site gateway sends the donor back to; a WordPress nonce cannot be sent here. give_clean() unslashes and sanitizes with sanitize_text_field(), and drops serialized data.

        $session = new DonationAccessor();
        $session->delete("postDataFor{$paymentGatewayId}");
    }

    /**
     * Return whether or not we are viewing donation confirmation view or not.
     *
     * @since 2.7.0
     * @return bool
     */
    public static function isConfirming()
    {
        return FormUtils::isViewingFormReceipt() && isset($_GET['payment-confirmation']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- donation confirmation page that an off-site gateway sends the donor back to; a WordPress nonce cannot be sent here.
    }
}
