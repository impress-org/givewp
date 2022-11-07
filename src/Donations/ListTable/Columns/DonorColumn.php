<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

/**
 * @extends ModelColumn<Donation>
 */
class DonorColumn extends ModelColumn
{

    protected $sortColumn = ['lastName', 'firstName'];

    /**
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'donor';
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donor Name', 'give');
    }

    /**
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-donors&view=overview&id={$model->donorId}"),
            __('View donor information', 'give'),
            trim("$model->firstName $model->lastName")
        );
    }
}
