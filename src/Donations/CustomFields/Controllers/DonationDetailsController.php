<?php

namespace Give\Donations\CustomFields\Controllers;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\Donations\CustomFields\Views\DonationDetailsView;
use Give\Donations\Models\Donation;

/**
 * TODO: move into donations domain
 * @since 3.0.0
 */
class DonationDetailsController
{
    /**
     * @since 3.0.0
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
            return $field->shouldShowInAdmin() && !$field->shouldStoreAsDonorMeta();
        });

        return (new DonationDetailsView($donation, $fields))->render();
    }
}
