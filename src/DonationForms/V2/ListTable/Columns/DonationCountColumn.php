<?php

declare(strict_types=1);

namespace Give\DonationForms\V2\ListTable\Columns;

use Give\DonationForms\DonationQuery;
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
     * @unreleased Add skeleton placeholder support to improve performance
     * @since 3.14.0 Use the "getDonationCount()" method from progress bar model to ensure the correct donation count will be used
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param DonationForm $model
     */
    public function getCellValue($model): string
    {
        $totalDonations = $this->getTotalDonationsValue($model);

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
            '<a class="column-donations" href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-payment-history&form_id=$model->id"),
            __('Visit donations page', 'give'),
            give_is_donations_column_async_on_admin_form_list_views() ? give_get_skeleton_placeholder_for_async_data('1rem') : $label
        );
    }

    /**
     * @unreleased
     */
    private function getTotalDonationsValue($model)
    {
        if (give_is_enabled_stats_cache_on_admin_form_list_views()) {
            // Return meta keys that store the aggregated values
            return $model->totalNumberOfDonations;
        }

        // Return data retrieved in real-time from DB
        return (new ProgressBarModel(['ids' => [$model->id]]))->getDonationCount();
    }
}
