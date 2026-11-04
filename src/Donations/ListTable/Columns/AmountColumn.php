<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\MoneyFormatter;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Donation>
 */
class AmountColumn extends ModelColumn
{

    protected $sortColumn = 'COALESCE(CAST(amount AS DECIMAL) / COALESCE(CAST(exchangeRate AS DECIMAL), 1), 0)';
    
    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'amount';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Amount', 'give');
    }

    /**
     * @since TBD Format with the shared money formatter; $locale is kept for add-on filters and no longer used.
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        return sprintf(
            '<div class="amount"><span>%s</span></div>',
            MoneyFormatter::format($model->amount)
        );
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     */
    public function getKind(): string
    {
        return ColumnKind::MONEY;
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getRawValue($model)
    {
        return MoneyFormatter::raw($model->amount);
    }
}
