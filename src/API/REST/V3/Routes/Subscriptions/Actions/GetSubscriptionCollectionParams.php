<?php

namespace Give\API\REST\V3\Routes\Subscriptions\Actions;

use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @since 4.8.0
 */
class GetSubscriptionCollectionParams
{
    /**
     * @since 4.8.0
     */
    public function __invoke(): array
    {
        return [
            'sort' => [
                'description' => esc_html__('Sort field for subscription results', 'give'),
                'type' => 'string',
                'default' => 'id',
                'enum' => [
                    'id',
                    'createdAt',
                    'renewsAt',
                    'status',
                    'amount',
                    'feeAmountRecovered',
                    'donorId',
                    'firstName',
                    'lastName',
                ],
            ],
            'direction' => [
                'description' => esc_html__('Sort direction for subscription results', 'give'),
                'type' => 'string',
                'default' => 'DESC',
                'enum' => ['ASC', 'DESC'],
            ],
            'mode' => [
                'description' => esc_html__('Subscription mode (live or test)', 'give'),
                'type' => 'string',
                'default' => 'live',
                'enum' => ['live', 'test'],
            ],
            'status' => [
                'description' => esc_html__('Filter subscriptions by status', 'give'),
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                    'enum' => ['any', ...array_values(SubscriptionStatus::toArray())],
                ],
                'default' => ['any'],
            ],
            'campaignId' => [
                'description' => esc_html__('Filter subscriptions by campaign ID', 'give'),
                'type' => 'integer',
                'default' => 0,
            ],
            'donorId' => [
                'description' => esc_html__('Filter subscriptions by donor ID', 'give'),
                'type' => 'integer',
                'default' => 0,
            ],
        ];
    }
}
