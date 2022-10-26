<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use DateTime;
use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

/**
 * @extends ModelColumn<Donation>
 */
class CreatedAtColumn extends ModelColumn
{
    public $sortColumn = 'createdAt';

    /**
     * @inheritDoc
     */
    public function getId(): string
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
    public function getCellValue($model): DateTime
    {
        return $model->createdAt;
    }
}
