<?php

namespace Give\DonationForms\AsyncData;

use Give\DonationForms\DonationQuery;
use Give\MultiFormGoals\ProgressBar\Model as ProgressBarModel;

/**
 * @unreleased
 */
class AsyncDataHelpers
{
    /**
     * @unreleased
     */
    public static function getFormDonationsCountValue($formId): int
    {
        return (new ProgressBarModel(['ids' => [$formId]]))->getDonationCount();
    }

    /**
     * @unreleased
     */
    public static function getFormRevenueValue($formId): int
    {
        return (new DonationQuery())->form($formId)->sumIntendedAmount();
    }

    /**
     * @unreleased
     */
    public static function getSkeletonPlaceholder($width = '100%', $height = '0.7rem')
    {
        return '<span class="give-skeleton js-give-async-data" style="width: ' . $width . '; height: ' . $height . ';"></span>';
    }
}
