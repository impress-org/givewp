<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

use function Give\Donations\Admin\DonationsList\Columns\__;

/**
 * @extends ModelColumn<Donation>
 */
class IdColumn extends ModelColumn
{
    public $sortColumn = 'id';

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'id';
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('ID', 'give');
    }

    /**
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): int
    {
        return $model->id;
    }
}
