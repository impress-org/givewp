<?php

namespace Give\API\REST\V3\Routes\Subscriptions\Actions;

use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @since 4.8.0
 */
class GetSubscriptionItemSchema
{
    /**
     * @since 4.8.0
     */
    public function __invoke(): array
    {
        return [
            'title' => 'givewp/subscription',
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => esc_html__('Subscription ID', 'give'),
                ],
                'donorId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Donor ID', 'give'),
                    'required' => true,
                ],
                'donationFormId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Donation form ID', 'give'),
                    'required' => true,
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
                    'required' => true,
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
                    'required' => true,
                ],
                'period' => [
                    'type' => 'string',
                    'description' => esc_html__('Subscription billing period', 'give'),
                    'enum' => array_values(SubscriptionPeriod::toArray()),
                    'required' => true,
                ],
                'frequency' => [
                    'type' => 'integer',
                    'description' => esc_html__('Billing frequency', 'give'),
                    'required' => true,
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
                    'required' => true,
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
                    'oneOf' => [
                        [
                            'type' => 'string',
                            'description' => esc_html__('Subscription creation date as ISO string', 'give'),
                            'format' => 'date-time',
                        ],
                        [
                            'type' => 'object',
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
                        ],
                        [
                            'type' => 'null',
                        ],
                    ],
                ],
                'renewsAt' => [
                    'oneOf' => [
                        [
                            'type' => 'string',
                            'description' => esc_html__('Next renewal date as ISO string', 'give'),
                            'format' => 'date-time',
                        ],
                        [
                            'type' => 'object',
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
                        ],
                        [
                            'type' => 'null',
                        ],
                    ],
                ],
                'projectedAnnualRevenue' => [
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
                    'description' => esc_html__('Projected annual revenue for this subscription', 'give'),
                    'readOnly' => true,
                ],
            ],
        ];
    }
}
