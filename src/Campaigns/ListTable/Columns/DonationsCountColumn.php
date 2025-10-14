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

        $label = sprintf(
            '<div class="donationCount"><span>%s</span></div>',
            $totalDonations
        );

        return apply_filters("givewp_list_table_cell_value_{$this::getId()}_content", $label, $model, $this);
    }
}
