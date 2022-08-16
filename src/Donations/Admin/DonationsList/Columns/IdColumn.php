<?php

declare(strict_types=1);

namespace Give\Donations\Admin\DonationsList\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

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
    public function getCellValue($model)
    {
        return $model->id;
    }
}
