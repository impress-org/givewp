<?php

namespace Give\DonationForms\V2\ListTable\Columns;

use Give\DonationForms\Repositories\DonationFormDataRepository;
use Give\DonationForms\V2\Models\DonationForm;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\CountFormatter;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since TBD
 */
class DonorCountColumn extends ModelColumn
{
    protected $useData = true;

    /**
     * @since TBD
     */
    public static function getId(): string
    {
        return 'donorCount';
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
     * @param DonationForm $model
     */
    public function getCellValue($model): string
    {
        // Read this way, as the other count columns do, so the signature stays getCellValue($model).
        $locale = func_num_args() > 1 ? (string)func_get_arg(1) : '';
        /**
         * @var DonationFormDataRepository $data
         */
        $data = $this->getListTableData();

        $label = sprintf(
            '<div class="donorsCount"><span>%s</span></div>',
            CountFormatter::format($data->getDonorsCount($model), $locale)
        );

        return $label;
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
     * @param DonationForm $model
     */
    public function getRawValue($model)
    {
        /**
         * @var DonationFormDataRepository $data
         */
        $data = $this->getListTableData();

        return $data->getDonorsCount($model);
    }
}
