<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 4.0.0
 */
class TitleColumn extends ModelColumn
{
    protected $sortColumn = 'title';

    /**
     * @since 4.0.0
     */
    public static function getId(): string
    {
        return 'title';
    }

    /**
     * @since 4.0.0
     */
    public function getLabel(): string
    {
        return __('Name', 'give');
    }

    /**
     * @since TBD Drop the aria-label so the link is named by its visible text.
     * @since 4.0.0
     *
     * @param Campaign $model
     */
    public function getCellValue($model): string
    {
        return sprintf(
            '<a href="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-campaigns&id=$model->id"),
            $model->title
        );
    }
}
