<?php

namespace Give\Donations\CustomFields\Controllers;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\Donations\CustomFields\Views\DonationDetailsView;
use Give\Donations\Models\Donation;

/**
 * @since 3.0.0
 */
class DonationDetailsController
{
    /**
     * @since 4.0.0 return early if no form is found
     * @since 3.0.0
     */
    public function show(int $donationID): string
    {
        /** @var Donation $donation */
        $donation = Donation::find($donationID);

        if (give(DonationFormRepository::class)->isLegacyForm($donation->formId)) {
            return '';
        }

        $form = DonationForm::find($donation->formId);

        if (!$form) {
            return '';
        }

        $fields = array_filter($form->schema()->getFields(), static function ($field) {
            return $field->shouldShowInAdmin() && !$field->shouldStoreAsDonorMeta();
        });

        return (new DonationDetailsView($donation, $fields))->render();
    }
}
