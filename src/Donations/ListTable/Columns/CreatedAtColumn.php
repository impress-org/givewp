<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 *
 * @extends ModelColumn<Donation>
 */
class CreatedAtColumn extends ModelColumn
{

    protected $sortColumn = 'createdAt';

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'createdAt';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Created At', 'give');
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        $format = _x('m/d/Y \a\t g:i a', 'human-readable datetime format', 'give');

        return $model->createdAt->format($format);
    }
}
