<?php

namespace Give\NextGen\CustomFields\Controllers;

use Give\Donations\Models\Donation;
use Give\NextGen\CustomFields\Views\DonationDetailsView;
use Give\NextGen\DonationForm\Models\DonationForm;

/**
 * @unreleased
 */
class DonationDetailsController
{
    /**
     * @unreleased
     *
     * @param int $donationID
     *
     * @return void
     */
    public function show(int $donationID): void
    {
        $donation = Donation::find($donationID);
        $form = DonationForm::find($donation->formId);
        $fields = array_filter($form->schema()->getFields(), function($field) {
            return $field->shouldDisplayInAdmin() && ! $field->shouldStoreAsDonorMeta();
        });
        $view = new DonationDetailsView($donation, $fields);
        echo $view->render();
    }
}
