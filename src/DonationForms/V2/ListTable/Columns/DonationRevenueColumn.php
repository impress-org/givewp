<?php

declare(strict_types=1);

namespace Give\DonationForms\V2\ListTable\Columns;

use Give\DonationForms\DonationQuery;
use Give\DonationForms\V2\Models\DonationForm;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<DonationForm>
 */
class DonationRevenueColumn extends ModelColumn
{

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
        return __('Revenue', 'give');
    }

    /**
     * @unreleased Add skeleton placeholder support to improve performance
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param DonationForm $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        return sprintf(
            '<a class="column-earnings" href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-reports&tab=forms&legacy=true&form-id=$model->id"),
            __('Visit form reports page', 'give'),
            give_is_revenue_column_on_form_list_async() ? give_get_skeleton_placeholder_for_async_data('1rem') : $this->getRevenueValue($model, $locale)
        );
    }

    /**
     * @unreleased
     */
    private function getRevenueValue($model, $locale = '')
    {
        if (give_is_column_cache_on_form_list_enabled()) {
            // use meta keys that store the aggregated values
            return $model->totalAmountDonated->formatToLocale($locale);
        }

        // Return data retrieved in real-time from DB
        return (new DonationQuery())->form($model->id)->sumAmount();
    }
}
