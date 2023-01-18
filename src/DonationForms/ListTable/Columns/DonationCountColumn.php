<?php

declare(strict_types=1);

namespace Give\DonationForms\ListTable\Columns;

use Give\DonationForms\Models\DonationForm;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<DonationForm>
 */
class DonationCountColumn extends ModelColumn
{

    protected $sortColumn = 'CAST(formSales AS UNSIGNED)';

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
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param DonationForm $model
     */
    public function getCellValue($model): string
    {
        $totalDonations = $model->totalNumberOfDonations;

        $label = $totalDonations > 0
            ? sprintf(
                _n(
                    '%1$s donation',
                    '%1$s donations',
                    $totalDonations,
                    'give'
                ),
                $totalDonations
            ) : __('No donations', 'give');

        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-payment-history&form_id=$model->id"),
            __('Visit donations page', 'give'),
            $label
        );
    }
}
