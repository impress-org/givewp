<?php

declare(strict_types=1);

namespace Give\Subscriptions\ListTable\Columns;

use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\MoneyFormatter;
use Give\Framework\ListTable\ModelColumn;
use Give\Subscriptions\Models\Subscription;

/**
 * @since 4.12.0 add sort column
 * @since 2.24.0
 *
 * @extends ModelColumn<Subscription>
 */
class AmountColumn extends ModelColumn
{
    protected $sortColumn = 'amount';

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
     * @param Subscription $model
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
     * @param Subscription $model
     */
    public function getRawValue($model)
    {
        return MoneyFormatter::raw($model->amount);
    }
}
