<?php

declare(strict_types=1);

namespace Give\Donations\Admin\DonationsList\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\Enums\CellValueType;
use Give\Framework\ListTable\ModelColumn;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @extends ModelColumn<Donation>
 */
class AmountColumn extends ModelColumn
{
    /**
     * @inheritDoc
     */
    public $sortColumn = 'amount';

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'amount';
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Amount', 'give');
    }

    /**
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): Money
    {
        return $model->amount;
    }

    /**
     * @inheritDoc
     */
    public function getCellValueType(): CellValueType
    {
        return CellValueType::CURRENCY();
    }
}
