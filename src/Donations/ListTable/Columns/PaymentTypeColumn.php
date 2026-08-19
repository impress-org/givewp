<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Donation>
 */
class PaymentTypeColumn extends ModelColumn
{
    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'paymentType';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Payment Type', 'give');
    }

    /**
     * @since 4.10.0 Removed badge icon
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        $map = [
            'single' => [
                'class' => 'one-time',
                'label' => __('One-time', 'give'),
            ],
            'recurring' => [
                'class' => 'recurring',
                'label' => __('Recurring', 'give'),
            ],
        ];

        $donationType = $map[$model->type->isRecurring() ? 'recurring' : 'single'];

        return sprintf(
            '<div class="badge badge--%1$s"><p>%2$s</p></div>',
            $donationType['class'],
            $donationType['label']
        );
    }
}
