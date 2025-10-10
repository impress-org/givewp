<?php

declare(strict_types=1);

namespace Give\Subscriptions\ListTable\Columns;

use Give\Framework\ListTable\ModelColumn;
use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased add sort column
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
            $model->amount->formatToLocale($locale)
        );
    }
}
