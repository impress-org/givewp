<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
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
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        $template = '
            <div class="badge">
                <img role="img" aria-labelledby="badgeId-%1$d" class="icon icon--%2$s" src="%3$s" alt="%4$s" />
                <p id="badgeId-%1$d" class="badge__label">%5$s</p>
            </div>
        ';

        if ($model->type->isRecurring()) {
            return sprintf(
                $template,
                $model->id,
                'recurring',
                GIVE_PLUGIN_URL . 'assets/dist/images/list-table/recurring-donation-icon.svg',
                __('Recurring donation icon', 'give'),
                __('recurring', 'give')
            );
        }

        return sprintf(
            $template,
            $model->id,
            'onetime',
            GIVE_PLUGIN_URL . 'assets/dist/images/list-table/onetime-donation-icon.svg',
            __('One-time donation icon', 'give'),
            __('one-time', 'give')
        );
    }
}
