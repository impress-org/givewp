<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class DonationsCountColumn extends ModelColumn
{
    /**
     * @unreleased
     */
    public static function getId(): string
    {
        return 'donationsCount';
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('Donations', 'give');
    }

    /**
     * @unreleased
     *
     * @param Campaign $model
     */
    public function getCellValue($model): string
    {
        $content = apply_filters("givewp_list_table_cell_value_{$this::getId()}_content", '', $model, $this);

        if (empty($content)) {
            $content = self::getTotalDonationsLabel($model);
        }

        return sprintf(
            '<a class="column-donations-count-value" href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-payment-history&form_id=$model->id"),
            __('Visit donations page', 'give'),
            $content
        );
    }

    /**
     * @unreleased
     */
    public static function getTotalDonationsLabel(Campaign $campaign): string
    {
        $query = new CampaignDonationQuery($campaign);
        $totalDonations = $query->countDonations();

        return $totalDonations > 0
            ? sprintf(
                _n(
                    '%1$s donation',
                    '%1$s donations',
                    $totalDonations,
                    'give'
                ),
                $totalDonations
            ) : __('No donations', 'give');
    }
}
