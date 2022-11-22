<?php

declare(strict_types=1);

namespace Give\DonationForms\ListTable\Columns;

use Give\DonationForms\Models\DonationForm;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 *
 * @extends ModelColumn<DonationForm>
 */
class ShortcodeColumn extends ModelColumn
{
    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'shortcode';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Shortcode', 'give');
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     *
     * @param DonationForm $model
     */
    public function getCellValue($model): string
    {
        return sprintf(
            '<div class="shortcode">[give_form id="%d"]</div>',
            $model->id
        );
    }
}
