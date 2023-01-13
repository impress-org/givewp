<?php

declare(strict_types=1);

namespace Give\Subscriptions\ListTable\Columns;

use Give\Subscriptions\Models\Subscription;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 *
 * @extends ModelColumn<Subscription>
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
     * @param Subscription $model
     */
    public function getCellValue($model): int
    {
        return $model->id;
    }
}
