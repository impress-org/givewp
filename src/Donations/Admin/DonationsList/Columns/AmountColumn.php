<?php

declare(strict_types=1);

namespace Give\Donations\Admin\DonationsList\Columns;

use Give\Framework\ListTable\AdvancedColumn;
use Give\Framework\ListTable\Enums\CellValueType;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\Support\ValueObjects\Money;

class AmountColumn extends AdvancedColumn
{
    public function getId(): string
    {
        return 'amount';
    }

    public function getLabel(): string
    {
        return __('Amount', 'give');
    }

    public function modifyQuery(QueryBuilder $query)
    {
        $query->attachMeta('give_donationmeta', 'id', 'donation_id', '_give_payment_total', '_give_payment_currency');
    }

    public function getSortingKey(): string
    {
        return '_give_payment_total';
    }

    /**
     * @inheritDoc
     */
    public function isSortable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCellValue($row): Money
    {
        return new Money($row->_give_payment_total, $row->_give_payment_currency);
    }

    public function getCellValueType(): CellValueType
    {
        return CellValueType::CURRENCY();
    }
}
