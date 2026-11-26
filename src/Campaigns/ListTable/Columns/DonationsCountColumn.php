<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\CountFormatter;
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
     * @since TBD Write the count in the admin's locale.
     * @since 4.0.0
     *
     * @param Campaign $model
     */
    public function getCellValue($model): string
    {
        // Read this way so a subclass that overrides getCellValue($model) keeps working.
        $locale = func_num_args() > 1 ? (string)func_get_arg(1) : '';
        /**
         * @var CampaignsDataRepository $campaignsData
         */
        $campaignsData = $this->getListTableData();

        $totalDonations = $campaignsData->getDonationsCount($model);

        $label = sprintf(
            '<div class="donationCount"><span>%s</span></div>',
            CountFormatter::format($totalDonations, $locale)
        );

        return apply_filters("givewp_list_table_cell_value_{$this::getId()}_content", $label, $model, $this);
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
     * @param Campaign $model
     */
    public function getRawValue($model)
    {
        /**
         * @var CampaignsDataRepository $campaignsData
         */
        $campaignsData = $this->getListTableData();

        return $campaignsData->getDonationsCount($model);
    }
}
