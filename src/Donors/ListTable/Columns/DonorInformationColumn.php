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
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donor Information', 'give');
    }

    /**
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
                <img class="donorInformation__gravatar" src="%s" alt="donor name" loading="lazy" />
                <a href="%s">%s</a>
                <address class="donorInformation__email">%s</address>
            </div>
        ';

        $url = DonorsAdminPage::getDetailsPageUrl($model->id);

        return sprintf(
            $template,
            get_avatar_url($model->email, ['size' => 64]),
            $url,
            trim("$model->firstName $model->lastName"),
            $model->email
        );
    }
}
