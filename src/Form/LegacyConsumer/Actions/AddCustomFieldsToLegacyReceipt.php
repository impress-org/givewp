<?php

namespace Give\Form\LegacyConsumer\Actions;

use Give\Receipt\DonationReceipt;

/**
 * @unreleased
 */
class AddCustomFieldsToLegacyReceipt
{
    /**
     * @unreleased
     */
    public function __invoke(DonationReceipt $receipt)
    {

        $donation = \Give\Donations\Models\Donation::find($receipt->donationId);

        if (!$donation) {
            return;
        }

        $confirmationPageReceipt = $donation->receipt();
        $details = $confirmationPageReceipt->additionalDetails->getDetails();

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
