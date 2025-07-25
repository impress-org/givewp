<?php

namespace Give\API\REST\V3\Routes\Subscriptions\Actions;

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
                    //'updatedAt',
                    'status',
                    'amount',
                    //'feeAmountRecovered',
                    'donorId',
                    //'firstName',
                    //'lastName',
                ],
            ],
            'direction' => [
                'type' => 'string',
                'default' => 'DESC',
                'enum' => ['ASC', 'DESC'],
            ],
            // Note: 'mode' parameter exists for API consistency but isn't used in queries
            // since subscriptions table doesn't have a mode column
            'mode' => [
                'type' => 'string',
                'default' => 'live',
                'enum' => ['live', 'test'],
            ],
            'status' => [
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                    'enum' => [
                        'any',
                        'pending',
                        'active',
                        'expired',
                        'completed',
                        'failing',
                        'cancelled',
                        'suspended',
                        'paused',
                        'refunded',
                        'abandoned',
                    ],
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
