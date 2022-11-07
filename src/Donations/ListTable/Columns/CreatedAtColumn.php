<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

/**
 * @extends ModelColumn<Donation>
 */
class CreatedAtColumn extends ModelColumn
{

    protected $sortColumn = 'date';

    /**
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'createdAt';
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Created At', 'give');
    }

    /**
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        return wp_date(_x('m/d/Y \a\t g:i a', 'datetime format', 'give'), $model->createdAt->getTimestamp());
    }
}
