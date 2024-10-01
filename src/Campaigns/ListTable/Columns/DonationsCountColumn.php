<?php

namespace Give\Campaigns\ListTable\Columns;

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
        //return (string)$model->query()->count(); //Temp count

        $totalDonations = 0; //$model->totalNumberOfDonations;

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
            '<a class="column-donations-count-value" href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-payment-history&form_id=$model->id"),
            __('Visit donations page', 'give'),
            apply_filters("givewp_list_table_cell_value_{$this::getId()}_content", $label, $model, $this)
        );
    }
}
