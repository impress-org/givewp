<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 4.0.0
 */
class DonationsCountColumn extends ModelColumn
{
    protected $useData = true;

    /**
     * @since 4.0.0
     */
    public static function getId(): string
    {
        return 'donationsCount';
    }

    /**
     * @since 4.0.0
     */
    public function getLabel(): string
    {
        return __('Donations', 'give');
    }

    /**
     * @since 4.0.0
     *
     * @param Campaign $model
     */
    public function getCellValue($model): string
    {
        /**
         * @var CampaignsDataRepository $campaignsData
         */
        $campaignsData = $this->getListTableData();

        $totalDonations = $campaignsData->getDonationsCount($model);

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


        return apply_filters("givewp_list_table_cell_value_{$this::getId()}_content", $label, $model, $this);
    }
}
