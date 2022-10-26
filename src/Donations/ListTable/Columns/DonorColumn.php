<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

/**
 * @extends ModelColumn<Donation>
 */
class DonorColumn extends ModelColumn
{
    public $sortColumn = 'CONCAT(lastName, ",", firstName)';

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'donor_name';
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donor Name', 'give');
    }

    /**
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        return "$model->firstName $model->lastName";
    }
}
