<?php

declare(strict_types=1);

namespace Give\Donors\ListTable\Columns;

use Give\Donors\Models\Donor;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\MoneyFormatter;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 4.12.0 add sort column
 * @since 2.24.0
 *
 * @extends ModelColumn<Donor>
 */
class DonationRevenueColumn extends ModelColumn
{
    protected $sortColumn = 'CAST(totalAmountDonated AS DECIMAL)';

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'donationRevenue';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Total Given', 'give');
    }

    /**
     * @since TBD Format with the shared money formatter; $locale is kept for add-on filters and no longer used.
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Donor $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        return sprintf(
            '<div class="amount"><span>%s</span></div>',
            MoneyFormatter::format($model->totalAmountDonated)
        );
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     */
    public function getKind(): string
    {
        return ColumnKind::MONEY;
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     *
     * @param Donor $model
     */
    public function getRawValue($model)
    {
        return MoneyFormatter::raw($model->totalAmountDonated);
    }
}
