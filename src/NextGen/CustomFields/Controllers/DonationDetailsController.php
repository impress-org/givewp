<?php

namespace Give\NextGen\CustomFields\Controllers;

use Give\Donations\Models\Donation;
use Give\NextGen\CustomFields\Views\DonationDetailsView;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;

/**
 * @unreleased
 */
class DonationDetailsController
{
    /**
     * @unreleased
     *
     * @param  int  $donationID
     *
     * @return string
     */
    public function show(int $donationID): string
    {
        /** @var Donation $donation */
        $donation = Donation::find($donationID);

        if (give(DonationFormRepository::class)->isLegacyForm($donation->formId)) {
            return '';
        }

        /** @var DonationForm $form */
        $form = DonationForm::find($donation->formId);

        $fields = array_filter($form->schema()->getFields(), static function ($field) {
            return $field->shouldDisplayInAdmin() && !$field->shouldStoreAsDonorMeta();
        });

        return (new DonationDetailsView($donation, $fields))->render();
    }
}
