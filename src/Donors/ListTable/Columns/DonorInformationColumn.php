<?php

declare(strict_types=1);

namespace Give\Donors\ListTable\Columns;

use Give\Donors\DonorsAdminPage;
use Give\Donors\Models\Donor;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Donor>
 */
class DonorInformationColumn extends ModelColumn
{

    protected $sortColumn = ['firstName', 'lastName'];

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'donorInformation';
    }

    /**
     * @since 4.12.0 Update column label
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Name', 'give');
    }

    /**
     * @since 4.12.0 Remove gravatar from donor information column
     * @since 3.20.0 Use email to get avatar URL
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Donor $model
     */
    public function getCellValue($model): string
    {
        $template = '
            <div class="donorInformation">
                <a href="%s">%s</a>
                <span class="donorInformation__email">%s</span>
            </div>
        ';

        $url = DonorsAdminPage::getDetailsPageUrl($model->id);

        return sprintf(
            $template,
            $url,
            trim("$model->firstName $model->lastName"),
            $model->email
        );
    }
}
