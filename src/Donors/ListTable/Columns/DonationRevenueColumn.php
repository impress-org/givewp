<?php

declare(strict_types=1);

namespace Give\Donors\ListTable\Columns;

use Give\Donors\Models\Donor;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 *
 * @extends ModelColumn<Donor>
 */
class DonationRevenueColumn extends ModelColumn
{

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'donationRevenue';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Total Given', 'give');
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     *
     * @param Donor $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        return sprintf(
            '<div class="amount"><span>%s</span></div>',
            $model->totalAmountDonated->formatToLocale($locale)
        );
    }
}
