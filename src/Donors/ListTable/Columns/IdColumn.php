<?php

declare(strict_types=1);

namespace Give\Donors\ListTable\Columns;

use Give\Donors\Models\Donor;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Donor>
 */
class IdColumn extends ModelColumn
{

    protected $sortColumn = 'id';

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'id';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('ID', 'give');
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Donor $model
     */
    public function getCellValue($model): int
    {
        return $model->id;
    }
}
