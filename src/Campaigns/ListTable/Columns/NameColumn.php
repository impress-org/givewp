<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class NameColumn extends ModelColumn
{
    protected $sortColumn = 'name';

    /**
     * @unreleased
     */
    public static function getId(): string
    {
        return 'name';
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('Name', 'give');
    }

    /**
     * @unreleased
     *
     * @param Campaign $model
     */
    public function getCellValue($model): string
    {
        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-campaigns&id=$model->id"),
            __('Visit campaign page', 'give'),
            $model->title
        );
    }
}
