<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

/**
 * @extends ModelColumn<Donation>
 */
class PaymentTypeColumn extends ModelColumn
{
    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'payment_type';
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Payment Type', 'give');
    }

    /**
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        if ( $model->startsSubscription ) {
            return 'subscription';
        }

        if ( $model->subscriptionId || $model->status->isRenewal()) {
            return 'renewal';
        }

        return 'single';
    }
}
