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
class IdColumn extends ModelColumn
{

    protected $sortColumn = 'id';

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'id';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('ID', 'give');
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): int
    {
        return $model->id;
    }
}
