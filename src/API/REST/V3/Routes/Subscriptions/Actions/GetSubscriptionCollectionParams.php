<?php

namespace Give\API\REST\V3\Routes\Subscriptions\Actions;

use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @unreleased
 */
class GetSubscriptionCollectionParams
{
    /**
     * @unreleased
     */
    public function __invoke(): array
    {
        return [
            'sort' => [
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
                'type' => 'string',
                'default' => 'DESC',
                'enum' => ['ASC', 'DESC'],
            ],            
            'mode' => [
                'type' => 'string',
                'default' => 'live',
                'enum' => ['live', 'test'],
            ],
            'status' => [
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                    'enum' => ['any', ...array_values(SubscriptionStatus::toArray())],
                ],
                'default' => ['any'],
            ],
            'campaignId' => [
                'type' => 'integer',
                'default' => 0,
            ],
            'donorId' => [
                'type' => 'integer',
                'default' => 0,
            ],
        ];
    }
}
