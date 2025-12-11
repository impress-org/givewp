<?php

namespace Give\DonationForms\Controllers;

use Give\DonationForms\DataTransferObjects\DonationConfirmationReceiptViewRouteData;
use Give\DonationForms\ViewModels\DonationConfirmationReceiptViewModel;
use Give\Donations\Models\Donation;

class DonationConfirmationReceiptViewController
{
    /**
     * This renders the donation confirmation receipt view.
     *
     * @since 3.0.0
     */
    public function show(DonationConfirmationReceiptViewRouteData $data): string
    {
        if (!$data->receiptId) {
            return '';
        }

        $donation = give()->donations->getByReceiptId($data->receiptId);

        if (!$donation) {
            return '';
        }

        /**
         * Fires before the donation confirmation receipt view is rendered.
         *
         * @since 3.4.0
         *
         * @param Donation $donation
         */
        do_action('givewp_donation_confirmation_receipt_showing', $donation);

        ob_clean();
        return (new DonationConfirmationReceiptViewModel($donation))->render();
    }
}
