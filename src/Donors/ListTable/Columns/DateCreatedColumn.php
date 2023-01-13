<?php

declare(strict_types=1);

namespace Give\Donors\ListTable\Columns;

use Give\Donors\Models\Donor;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 *
 * @extends ModelColumn<Donor>
 */
class DateCreatedColumn extends ModelColumn
{

    protected $sortColumn = 'createdAt';

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'dateCreated';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Date Created', 'give');
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     *
     * @param Donor $model
     */
    public function getCellValue($model): string
    {
        $format = _x('m/d/Y \a\t g:i a', 'human-readable datetime format', 'give');

        return $model->createdAt->format($format);
    }
}
