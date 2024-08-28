<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class TitleColumn extends ModelColumn
{
    protected $sortColumn = 'title';

    /**
     * @unreleased
     */
    public static function getId(): string
    {
        return 'title';
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('Campaign', 'give');
    }

    /**
     * @unreleased
     */
    public function getCellValue($model): string
    {
        $id = 1; //$model->id;
        $title = 'Camping Title #1'; //$model->title

        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-campaigns&id=$id"),
            __('Visit campaign page', 'give'),
            $title
        );
    }
}
