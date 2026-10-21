<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Donation>
 */
class DonorColumn extends ModelColumn
{

    protected $sortColumn = ['firstName', 'lastName'];

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
     * @since 4.10.0 Updated column label
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donor', 'give');
    }

    /**
     * @since TBD Drop the aria-label so the link is named by its visible text.
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        return sprintf(
            '<a href="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-donors&view=overview&id=$model->donorId"),
            trim("$model->firstName $model->lastName")
        );
    }
}
