<?php

namespace Give\DonationForms\AsyncData;

use Give\DonationForms\DataTransferObjects\DonationFormGoalData;
use Give\DonationForms\V2\Models\DonationForm;
use Give\MultiFormGoals\ProgressBar\Model as ProgressBarModel;

/**
 * @since 3.16.0
 */
class AsyncDataHelpers
{
    /**
     * @since 3.16.0
     */
    public static function getFormDonationsCountValue($formId): int
    {
        return (new ProgressBarModel(['ids' => [$formId], 'statusList' => ['any']]))->getDonationCount();
    }

    /**
     * @since 4.2.0 return float value
     * @since 3.16.0
     */
    public static function getFormRevenueValue($formId): float
    {
        $donationForm = DonationForm::find($formId);
        $form = $donationForm->toV3Form();
        $goalData = new DonationFormGoalData($form->id, $form->settings);

        return $goalData->getCurrentAmount();
    }

    /**
     * @since 3.16.0
     */
    public static function getSkeletonPlaceholder($width = '100%', $height = '0.7rem'): string
    {
        return '<span class="give-skeleton js-give-async-data" style="width: ' . esc_attr($width) . '; height: ' . esc_attr($height) . ';"></span>';
    }
}
