<?php

namespace Give\API\REST\V3\Routes\Subscriptions\Actions;

use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @unreleased
 */
class GetSubscriptionItemSchema
{
    /**
     * @unreleased
     */
    public function __invoke(): array
    {
        return [
            'title' => 'subscription',
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => esc_html__('Subscription ID', 'give'),
                ],
                'donorId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Donor ID', 'give'),
                ],
                'donationFormId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Donation form ID', 'give'),
                ],
                'amount' => [
                    'type' => ['object', 'null'],
                    'properties' => [
                        'amount' => [
                            'type' => 'number',
                        ],
                        'amountInMinorUnits' => [
                            'type' => 'integer',
                        ],
                        'currency' => [
                            'type' => 'string',
                            'format' => 'text-field',
                        ],
                    ],
                    'description' => esc_html__('Subscription amount', 'give'),
                ],
                'feeAmountRecovered' => [
                    'type' => ['object', 'null'],
                    'properties' => [
                        'amount' => [
                            'type' => 'number',
                        ],
                        'amountInMinorUnits' => [
                            'type' => 'integer',
                        ],
                        'currency' => [
                            'type' => 'string',
                            'format' => 'text-field',
                        ],
                    ],
                    'description' => esc_html__('Fee amount recovered', 'give'),
                ],
                'status' => [
                    'type' => 'string',
                    'description' => esc_html__('Subscription status', 'give'),
                    'enum' => ['any', ...array_values(SubscriptionStatus::toArray())],
                ],
                'period' => [
                    'type' => 'string',
                    'description' => esc_html__('Subscription billing period', 'give'),
                    'enum' => array_values(SubscriptionPeriod::toArray()),
                ],
                'frequency' => [
                    'type' => 'integer',
                    'description' => esc_html__('Billing frequency', 'give'),
                ],
                'installments' => [
                    'type' => 'integer',
                    'description' => esc_html__('Number of installments (0 for unlimited)', 'give'),
                    'default' => 0,
                ],
                'transactionId' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Transaction ID', 'give'),
                    'format' => 'text-field',
                ],
                'gatewayId' => [
                    'type' => 'string',
                    'description' => esc_html__('Payment gateway ID', 'give'),
                    'format' => 'text-field',
                ],
                'gatewaySubscriptionId' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Gateway subscription ID', 'give'),
                    'format' => 'text-field',
                ],
                'mode' => [
                    'type' => 'string',
                    'description' => esc_html__('Subscription mode (live or test)', 'give'),
                    'default' => 'live',
                    'enum' => ['live', 'test'],
                ],
                'createdAt' => [
                    'type' => ['object', 'null'],
                    'properties' => [
                        'date' => [
                            'type' => 'string',
                            'description' => esc_html__('Date', 'give'),
                            'format' => 'date-time',
                        ],
                        'timezone' => [
                            'type' => 'string',
                            'description' => esc_html__('Timezone of the date', 'give'),
                            'format' => 'text-field',
                        ],
                        'timezone_type' => [
                            'type' => 'integer',
                            'description' => esc_html__('Timezone type', 'give'),
                        ],
                    ],
                    'description' => esc_html__('Subscription creation date', 'give'),
                    'format' => 'date-time',
                ],
                'renewsAt' => [
                    'type' => ['object', 'null'],
                    'properties' => [
                        'date' => [
                            'type' => 'string',
                            'description' => esc_html__('Date', 'give'),
                            'format' => 'date-time',
                        ],
                        'timezone' => [
                            'type' => 'string',
                            'description' => esc_html__('Timezone of the date', 'give'),
                            'format' => 'text-field',
                        ],
                        'timezone_type' => [
                            'type' => 'integer',
                            'description' => esc_html__('Timezone type', 'give'),
                        ],
                    ],
                    'description' => esc_html__('Next renewal date', 'give'),
                    'format' => 'date-time',
                ],
            ],
            'required' => [
                'id',
                'donorId',
                'donationFormId',
                'amount',
                'status',
                'period',
                'frequency',
                'gatewayId',
                'mode',
                'createdAt',
            ],
        ];
    }
}
