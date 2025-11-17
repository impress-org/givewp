<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 4.8.0
 *
 * @extends ModelColumn<Donation>
 */
class SubscriptionDonationTypeColumn extends ModelColumn
{
    /**
     * @since 4.8.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'subscriptionDonationType';
    }

    /**
     * @since 4.8.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Subscription Donation Type', 'give');
    }

    /**
     * @since 4.10.0 Updated badge classname
     * @since 4.8.0
     *
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        $map = [
            DonationType::SINGLE => [
                'class' => 'one-time',
                'label' => __('One-time donation', 'give'),
            ],
            DonationType::SUBSCRIPTION => [
                'class' => 'subscription',
                'label' => __('Initial donation', 'give'),
            ],
            DonationType::RENEWAL => [
                'class' => 'renewal',
                'label' => __('Renewal', 'give'),
            ],
        ];

        $donationType = $map[$model->type->getValue()];


        return sprintf(
            '<div class="badge badge--%1$s"><p>%2$s</p></div>',
            $donationType['class'],
            $donationType['label']
        );
    }
}
