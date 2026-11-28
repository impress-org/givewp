<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\CountFormatter;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since TBD
 */
class DonorsCountColumn extends ModelColumn
{
    protected $useData = true;

    /**
     * @since TBD
     */
    public static function getId(): string
    {
        return 'donorsCount';
    }

    /**
     * @since TBD
     */
    public function getLabel(): string
    {
        return __('Donors', 'give');
    }

    /**
     * @since TBD
     *
     * @param Campaign $model
     */
    public function getCellValue($model): string
    {
        // Read this way, as the other count columns do, so the signature stays getCellValue($model).
        $locale = func_num_args() > 1 ? (string)func_get_arg(1) : '';
        /**
         * @var CampaignsDataRepository $data
         */
        $data = $this->getListTableData();

        $label = sprintf(
            '<div class="donorsCount"><span>%s</span></div>',
            CountFormatter::format($data->getDonorsCount($model), $locale)
        );

        return apply_filters("givewp_list_table_cell_value_{$this::getId()}_content", $label, $model, $this);
    }

    /**
     * @since TBD
     */
    public function getKind(): string
    {
        return ColumnKind::COUNT;
    }

    /**
     * @since TBD
     *
     * @param Campaign $model
     */
    public function getRawValue($model)
    {
        /**
         * @var CampaignsDataRepository $data
         */
        $data = $this->getListTableData();

        return $data->getDonorsCount($model);
    }
}
