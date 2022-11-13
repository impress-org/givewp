<?php

declare(strict_types=1);

namespace Give\Donors\ListTable\Columns;

use Give\Donors\Models\Donor;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 *
 * @extends ModelColumn<Donor>
 */
class DonorInformationColumn extends ModelColumn
{

    protected $sortColumn = ['firstName', 'lastName'];

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'donorInformation';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donor Information', 'give');
    }

    /**
     * @unreleased
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

        return sprintf(
            $template,
            get_avatar_url($model->id, ['size' => 64]),
            admin_url("edit.php?post_type=give_forms&page=give-donors&view=overview&id=$model->id"),
            trim("$model->firstName $model->lastName"),
            $model->email
        );
    }
}
