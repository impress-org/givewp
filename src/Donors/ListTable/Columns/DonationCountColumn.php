<?php

declare(strict_types=1);

namespace Give\Donors\ListTable\Columns;

use Give\Donors\Models\Donor;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\CountFormatter;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Donor>
 */
class DonationCountColumn extends ModelColumn
{

    protected $sortColumn = 'totalNumberOfDonations';

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'donationCount';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donations', 'give');
    }

    /**
     * @since TBD Write the count in the admin's locale.
     * @since 4.12.0 Remove link from donation count column
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Donor $model
     */
    public function getCellValue($model): string
    {
        // Read this way so a subclass that overrides getCellValue($model) keeps working.
        $locale = func_num_args() > 1 ? (string)func_get_arg(1) : '';
        $totalDonations = $model->totalNumberOfDonations;

        return sprintf(
            '<div class="donationCount"><span>%s</span></div>',
            CountFormatter::format($totalDonations, $locale)
        );
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     */
    public function getKind(): string
    {
        return ColumnKind::COUNT;
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
        return (int) $model->totalNumberOfDonations;
    }
}
