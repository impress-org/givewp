<?php

declare(strict_types=1);

namespace Give\Subscriptions\ListTable\Columns;

use Give\Framework\ListTable\ModelColumn;
use Give\Subscriptions\Models\Subscription;

/**
 * @since 4.12.0 add sort column
 * @since 2.24.0
 *
 * @extends ModelColumn<Subscription>
 */
class DonorColumn extends ModelColumn
{
    protected $sortColumn = 'donorName';

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'donor';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donor name', 'give');
    }

    /**
     * @since TBD Name the link by its visible text, with a fallback for a donor with no name, and escape both.
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Subscription $model
     */
    public function getCellValue($model): string
    {
        $name = array_filter([
            $model->donor->firstName,
            $model->donor->lastName,
        ]);

        $donorName = trim(implode(' ', $name));

        return sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url("edit.php?post_type=give_forms&page=give-donors&view=overview&id=$model->donorId")),
            esc_html($donorName !== '' ? $donorName : __('View donor information', 'give'))
        );
    }
}
