<?php

namespace Give\DonationForms\Controllers;

use Give\DonationForms\DataTransferObjects\DonationConfirmationReceiptViewRouteData;
use Give\DonationForms\ViewModels\DonationConfirmationReceiptViewModel;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\QueryBuilder\QueryBuilder;

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

        $donation = $this->getDonationByReceiptId($data->receiptId);

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

    /**
     * @since 3.0.0
     *
     * @return Donation|null
     */
    private function getDonationByReceiptId(string $receiptId)
    {
        return give()->donations->prepareQuery()
            ->where('post_type', 'give_payment')
            ->where('ID', function (QueryBuilder $builder) use ($receiptId) {
                $builder
                    ->select('donation_id')
                    ->from('give_donationmeta')
                    ->where('meta_key', DonationMetaKeys::PURCHASE_KEY()->getValue())
                    ->where('meta_value', $receiptId);
            })->get();
    }
}
