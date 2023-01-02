<?php

namespace Give\NextGen\CustomFields\Controllers;

use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\NextGen\CustomFields\Views\DonorDetailsView;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;

use function array_reduce;

/**
 * @unreleased
 */
class DonorDetailsController
{
    /**
     * @unreleased
     */
    public function show(Donor $donor): string
    {
        $forms = $this->getUniqueDonationFormsForDonor($donor);

        if (!$forms) {
            return '';
        }

        $fields = array_reduce($forms, function ($fields, DonationForm $form) {
            return $fields + $this->getDisplayedDonorMetaFieldsForForm($form);
        }, []);

        return (new DonorDetailsView($donor, $fields))->render();
    }

    /**
     * @unreleased
     *
     * @param  Donor  $donor
     *
     * @return DonationForm[]
     */
    protected function getUniqueDonationFormsForDonor(Donor $donor): array
    {
        $formIds = array_map(static function (Donation $donation) {
            return $donation->formId;
        }, $donor->donations);

        $formIds = array_filter($formIds, static function ($formId) {
            return !give(DonationFormRepository::class)->isLegacyForm($formId);
        });

        return array_map(static function ($formId) {
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
