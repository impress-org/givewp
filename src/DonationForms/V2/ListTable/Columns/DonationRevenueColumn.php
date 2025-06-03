<?php

declare(strict_types=1);

namespace Give\DonationForms\V2\ListTable\Columns;

use Give\DonationForms\Repositories\DonationFormDataRepository;
use Give\DonationForms\V2\Models\DonationForm;
use Give\Framework\ListTable\ModelColumn;
use Give\Framework\Support\ValueObjects\Money;

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
     * @since 4.3.0 use DonationFormDataRepository
     * @since 3.16.0 Add filter to change the cell value content
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param DonationForm $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        /**
         * @var DonationFormDataRepository $donationFormData
         */
        $donationFormData = $this->getListTableData();

        $revenue = Money::fromDecimal($donationFormData->getRevenue($model), give_get_currency())->formatToLocale();

        return sprintf(
            '<a class="column-earnings-value" href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-reports&tab=forms&legacy=true&form-id=$model->id"),
            __('Visit form reports page', 'give'),
            apply_filters("givewp_list_table_cell_value_{$this::getId()}_content", $revenue, $model, $this)
        );
    }
}
