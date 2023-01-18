<?php

declare(strict_types=1);

namespace Give\Subscriptions\ListTable\Columns;

use Give\Subscriptions\Models\Subscription;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Subscription>
 */
class BillingPeriodColumn extends ModelColumn
{

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'billingPeriod';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Billing period', 'give');
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Subscription $model
     */
    public function getCellValue($model): string
    {
        $label = $model->period->label($model->frequency);

        return ($model->frequency > 1) ? sprintf( $label, $model->frequency) : $label;
    }
}
