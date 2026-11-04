<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\MoneyFormatter;
use Give\Framework\ListTable\ModelColumn;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @since 4.0.0
 */
class RevenueColumn extends ModelColumn
{
    protected $useData = true;
    protected $sortColumn = 'revenue';

    /**
     * @since 4.0.0
     */
    public static function getId(): string
    {
        return 'revenue';
    }

    /**
     * @since 4.0.0
     */
    public function getLabel(): string
    {
        return __('Revenue', 'give');
    }

    /**
     * @since TBD Format with the shared money formatter.
     * @since 4.0.0
     *
     * @param Campaign $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        /**
         * @var CampaignsDataRepository $campaignsData
         */
        $campaignsData = $this->getListTableData();

        $revenue = MoneyFormatter::fromDecimal($campaignsData->getRevenue($model));

        return sprintf(
            '<a class="column-earnings-value" href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-campaigns&id=$model->id"),
            __('Visit form reports page', 'give'),
            apply_filters(
                "givewp_list_table_cell_value_{$this::getId()}_content",
                $revenue,
                $model,
                $this
            )
        );
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     */
    public function getKind(): string
    {
        return ColumnKind::MONEY;
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     *
     * @param Campaign $model
     *
     * @return array{amount: string, currency: string}
     */
    public function getRawValue($model)
    {
        /**
         * @var CampaignsDataRepository $campaignsData
         */
        $campaignsData = $this->getListTableData();

        return MoneyFormatter::raw(Money::fromDecimal($campaignsData->getRevenue($model), give_get_currency()));
    }
}
