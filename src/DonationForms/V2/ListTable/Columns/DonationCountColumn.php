<?php

declare(strict_types=1);

namespace Give\DonationForms\V2\ListTable\Columns;

use Give\DonationForms\V2\Models\DonationForm;
use Give\Framework\ListTable\ModelColumn;
use Give\MultiFormGoals\ProgressBar\Model as ProgressBarModel;

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
     * @unreleased Replace "getDonationCount()" method with skeleton placeholder to improve performance
     * @since 3.14.0 Use the "getDonationCount()" method from progress bar model to ensure the correct donation count will be used
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param DonationForm $model
     */
    public function getCellValue($model): string
    {
        return sprintf(
            '<a class="column-donations" href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-payment-history&form_id=$model->id"),
            __('Visit donations page', 'give'),
            give_get_skeleton_placeholder_for_async_data('1rem')
        );
    }
}
