<?php

namespace Give\NextGen\CustomFields\Controllers;

use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\NextGen\CustomFields\Views\DonorDetailsView;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give_Donor as LegacyDonor;

/**
 * @unreleased
 */
class DonorDetailsController
{
    /**
     * @unreleased
     *
     * @param LegacyDonor $legacyDonor
     *
     * @return void
     */
    public function show(LegacyDonor $legacyDonor): void
    {
        $donor = Donor::find($legacyDonor->id);

        $forms = $this->getUniqueDonationFormsForDonor($donor);

        $fields = array_reduce($forms, function($fields, DonationForm $form) {
            return $fields + $this->getDisplayedDonorMetaFieldsForForm($form);
        }, []);

        $view = new DonorDetailsView($donor, $fields);
        echo $view->render();
    }

    /**
     * @unreleased
     *
     * @param Donor $donor
     *
     * @return array
     */
    protected function getUniqueDonationFormsForDonor(Donor $donor): array
    {
        $formIds = array_map(function(Donation $donation) {
            return $donation->formId;
        }, $donor->donations);

        return array_map(function($formId) {
            return DonationForm::find($formId);
        }, array_unique($formIds));
    }

    /**
     * @unreleased
     *
     * @param DonationForm $form
     *
     * @return array
     */
    protected function getDisplayedDonorMetaFieldsForForm(DonationForm $form): array
    {
        return array_filter($form->schema()->getFields(), function($field) {
            return $field->shouldDisplayInAdmin() && $field->shouldStoreAsDonorMeta();
        });
    }
}
