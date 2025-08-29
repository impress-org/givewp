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
            'title' => 'givewp/subscription',
            'type' => 'object',
            'properties' => [
                'mode' => [
                    'type' => 'string',
                    'description' => esc_html__('Subscription mode (live or test)', 'give'),
                    'default' => 'live',
                    'enum' => ['live', 'test'],
                ],
                'id' => [
                    'type' => 'integer',
                    'description' => esc_html__('Subscription ID', 'give'),
                ],
                'donationFormId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Donation form ID', 'give'),
                    'required' => true,
                ],
                'donorId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Donor ID', 'give'),
                    'required' => true,
                ],
                'firstName' => [
                    'type' => 'string',
                    'description' => esc_html__('Donor first name', 'give'),
                    'format' => 'text-field',
                ],
                'lastName' => [
                    'type' => 'string',
                    'description' => esc_html__('Donor last name', 'give'),
                    'format' => 'text-field',
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
                'gateway' => [
                    'type' => 'array',
                    'description' => esc_html__('Payment gateway details', 'give'),
                ],
                'createdAt' => [
                    'type' => ['string', 'null'],
                    'description' => sprintf(
                        /* translators: %1$s: Example date string, %2$s: WordPress documentation URL */
                        esc_html__('Subscription creation date in ISO 8601 format (e.g., "%1$s"). Follows WordPress REST API date format standards. See %2$s for more information.', 'give'),
                        '2025-01-01T12:00:00+00:00',
                        '<a href="https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#format" target="_blank">WordPress REST API Date and Time</a>'
                    ),
                    'format' => 'date-time',
                    'example' => '2025-01-01T12:00:00+00:00',
                ],
                'renewsAt' => [
                    'type' => ['string', 'null'],
                    'description' => sprintf(
                        /* translators: %1$s: Example date string, %2$s: WordPress documentation URL */
                        esc_html__('Next renewal date in ISO 8601 format (e.g., "%1$s"). Follows WordPress REST API date format standards. See %2$s for more information.', 'give'),
                        '2025-01-01T12:00:00+00:00',
                        '<a href="https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#format" target="_blank">WordPress REST API Date and Time</a>'
                    ),
                    'format' => 'date-time',
                    'example' => '2025-01-01T12:00:00+00:00',
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
