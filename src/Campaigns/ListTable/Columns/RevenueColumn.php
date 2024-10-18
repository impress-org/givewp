<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class RevenueColumn extends ModelColumn
{
    protected $sortColumn = 'revenue';

    /**
     * @unreleased
     */
    public static function getId(): string
    {
        return 'revenue';
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('Revenue', 'give');
    }

    /**
     * @unreleased
     *
     * @param Campaign $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        $query = new CampaignDonationQuery($model);
        $revenue = give_currency_filter(give_format_amount($query->sumIntendedAmount()));

        return sprintf(
            '<a class="column-earnings-value" href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-reports&tab=forms&legacy=true&form-id=$model->id"),
            __('Visit form reports page', 'give'),
            apply_filters("givewp_list_table_cell_value_{$this::getId()}_content",
                $revenue, $model, $this)
        );
    }
}
