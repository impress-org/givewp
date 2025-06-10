<?php

namespace Give\Form\LegacyConsumer\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Helpers\Form\Utils;
use Give\Receipt\DonationReceipt;

/**
 * @since 4.3.0
 */
class AddCustomFieldsToLegacyReceipt
{
    /**
     * @since 4.3.0
     */
    public function __invoke(DonationReceipt $receipt)
    {

        $donation = Donation::find($receipt->donationId);

        // make sure we have a valid donation
        if (!$donation) {
            return;
        }

        $details = $donation->receipt()->additionalDetails->getDetails();

        if (empty($details)) {
            return;
        }

        $index = 0;
        $section = $receipt->getSections()[DonationReceipt::ADDITIONALINFORMATIONSECTIONID];

        foreach ($details as $detail) {
            $index++;
            // line item will fail if the value is null/empty so we need to check for it and add an empty space instead
            $section->addLineItem(
                [
                    'id' => "givewp-custom-field-{$index}",
                    'label' => !empty($detail->label) ? $detail->label : ' ',
                    'value' => !empty($detail->value) ? $detail->value : ' ',
                ]
            );
        }
    }
}
