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
class StatusColumn extends ModelColumn
{
    protected $sortColumn = 'status';

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'status';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Status', 'give');
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
        return sprintf(
        '<div class="statusBadge statusBadge--%1$s"><p>%2$s</p></div>',
            $model->status->getValue(),
            $model->status->label()
        );
    }
}
