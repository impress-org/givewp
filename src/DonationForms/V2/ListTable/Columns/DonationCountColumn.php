<?php

declare(strict_types=1);

namespace Give\DonationForms\V2\ListTable\Columns;

use Give\DonationForms\Repositories\DonationFormDataRepository;
use Give\DonationForms\V2\Models\DonationForm;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\CountFormatter;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<DonationForm>
 */
class DonationCountColumn extends ModelColumn
{

    protected $sortColumn = 'CAST(formSales AS UNSIGNED)';

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'donationCount';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donations', 'give');
    }

    /**
     * @since TBD Write the count in the admin's locale.
     * @since 4.3.0 use DonationFormDataRepository
     * @since 3.16.0 Add filter to change the cell value content
     * @since 3.14.0 Use the "getDonationCount()" method from progress bar model to ensure the correct donation count will be used
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param DonationForm $model
     */
    public function getCellValue($model): string
    {
        // Read this way so a subclass that overrides getCellValue($model) keeps working.
        $locale = func_num_args() > 1 ? (string)func_get_arg(1) : '';
        /**
         * @var DonationFormDataRepository $donationFormData
         */
        $donationFormData = $this->getListTableData();

        $totalDonations = $donationFormData->getDonationsCount($model);

        return sprintf(
            '<div class="donationCount"><span>%s</span></div>',
            CountFormatter::format($totalDonations, $locale)
        );
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
     * @param DonationForm $model
     */
    public function getRawValue($model)
    {
        /**
         * @var DonationFormDataRepository $donationFormData
         */
        $donationFormData = $this->getListTableData();

        return $donationFormData->getDonationsCount($model);
    }
}
