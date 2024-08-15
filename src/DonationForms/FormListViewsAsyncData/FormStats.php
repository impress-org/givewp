<?php

namespace Give\DonationForms\FormListViewsAsyncData;

use Give\DonationForms\DonationQuery;
use Give\MultiFormGoals\ProgressBar\Model as ProgressBarModel;

/**
 * @unreleased
 */
class FormStats
{
    /**
     * @unreleased
     */
    public static function getDonationsCountValue($formId): int
    {
        return (new ProgressBarModel(['ids' => [$formId]]))->getDonationCount();
    }

    /**
     * @unreleased
     */
    public static function getRevenueValue($formId): int
    {
        return (new DonationQuery())->form($formId)->sumIntendedAmount();
    }
}
