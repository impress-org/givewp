<?php

declare(strict_types=1);

namespace Give\Donations\DonationsListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\ListTable\ModelColumn;

/**
 * @extends ModelColumn<Donation>
 */
class StatusColumn extends ModelColumn
{
    public $sortColumn = 'status';

    public function getId(): string
    {
        return 'status';
    }

    public function getLabel(): string
    {
        return __('Status', 'give');
    }

    /**
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): DonationStatus
    {
        return $model->status;
    }
}
