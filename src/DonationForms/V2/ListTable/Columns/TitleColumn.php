<?php

declare(strict_types=1);

namespace Give\DonationForms\V2\ListTable\Columns;

use Give\DonationForms\V2\Models\DonationForm;
use Give\Framework\ListTable\ModelColumn;
use Give\Helpers\Language;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<DonationForm>
 */
class TitleColumn extends ModelColumn
{

    protected $sortColumn = 'title';

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'title';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Name', 'give');
    }

    /**
     * @unreleased Add locale support
     * @since 3.0.0 remove html tags from title
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param DonationForm $model
     */
    public function getCellValue($model): string
    {
        return sprintf(
            '<a href="%s" class="giveDonationFormsLink">%s</a>',
            add_query_arg(['locale' => Language::getLocale()], get_edit_post_link($model->id)),
            wp_strip_all_tags($model->title)
        );
    }
}
