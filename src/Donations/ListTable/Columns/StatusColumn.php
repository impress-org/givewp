<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\ListTable\ModelColumn;

/**
 * @extends ModelColumn<Donation>
 */
class StatusColumn extends ModelColumn
{
    protected $sortColumn = 'status';

    /**
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'status';
    }

    /**
     * @inheritDoc
     */
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
