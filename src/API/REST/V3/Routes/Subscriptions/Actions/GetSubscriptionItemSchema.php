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
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'givewp/subscription',
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => esc_html__('Subscription ID', 'give'),
                ],
                'mode' => [
                    'type' => 'string',
                    'description' => esc_html__('Subscription mode (live or test)', 'give'),
                    'default' => 'live',
                    'enum' => ['live', 'test'],
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
                        'value' => [
                            'type' => 'number',
                            'description' => esc_html__('Amount in decimal format', 'give'),
                        ],
                        'valueInMinorUnits' => [
                            'type' => 'integer',
                            'description' => esc_html__('Amount in minor units (cents)', 'give'),
                        ],
                        'currency' => [
                            'type' => 'string',
                            'format' => 'text-field',
                            'description' => esc_html__('Currency code (e.g., USD, EUR)', 'give'),
                        ],
                    ],
                    'required' => ['value', 'valueInMinorUnits', 'currency'],
                    'description' => esc_html__('Subscription amount', 'give'),
                    'required' => true,
                ],
                'feeAmountRecovered' => [
                    'type' => ['object', 'null'],
                    'properties' => [
                        'value' => [
                            'type' => 'number',
                            'description' => esc_html__('Fee amount in decimal format', 'give'),
                        ],
                        'valueInMinorUnits' => [
                            'type' => 'integer',
                            'description' => esc_html__('Fee amount in minor units (cents)', 'give'),
                        ],
                        'currency' => [
                            'type' => 'string',
                            'format' => 'text-field',
                            'description' => esc_html__('Currency code (e.g., USD, EUR)', 'give'),
                        ],
                    ],
                    'required' => ['value', 'valueInMinorUnits', 'currency'],
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
                'gatewaySubscriptionId' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Gateway subscription ID', 'give'),
                    'format' => 'text-field',
                ],
                'gatewayId' => [
                    'type' => 'string',
                    'description' => esc_html__('Payment gateway ID', 'give'),
                    'format' => 'text-field',
                    'required' => true,
                ],
                'gateway' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => [
                            'type' => ['string', 'null'],
                            'description' => esc_html__('Gateway ID', 'give'),
                        ],
                        'name' => [
                            'type' => ['string', 'null'],
                            'description' => esc_html__('Gateway name', 'give'),
                        ],
                        'label' => [
                            'type' => ['string', 'null'],
                            'description' => esc_html__('Payment method label', 'give'),
                        ],
                        'subscriptionUrl' => [
                            'type' => ['string', 'null'],
                            'format' => 'uri',
                            'description' => esc_html__('Gateway dashboard subscription URL', 'give'),
                        ],
                        'canSync' => [
                            'type' => ['boolean', 'null'],
                            'description' => esc_html__('Whether the gateway supports transaction synchronization', 'give'),
                        ],
                    ],
                    'description' => esc_html__('Payment gateway details. Properties will be null if gateway is not available or not registered.', 'give'),
                    'readOnly' => true,
                ],
                'createdAt' => [
                    'type' => ['string', 'null'],
                    'description' => sprintf(
                        /* translators: %s: WordPress documentation URL */
                        esc_html__('Subscription creation date in ISO 8601 format. Follows WordPress REST API date format standards. See %s for more information.', 'give'),
                        '<a href="https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#format" target="_blank">WordPress REST API Date and Time</a>'
                    ),
                    'format' => 'date-time',
                    'example' => '2025-09-02T20:27:02',
                ],
                'renewsAt' => [
                    'type' => ['string', 'null'],
                    'description' => sprintf(
                        /* translators: %s: WordPress documentation URL */
                        esc_html__('Next renewal date in ISO 8601 format. Follows WordPress REST API date format standards. See %s for more information.', 'give'),
                        '<a href="https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#format" target="_blank">WordPress REST API Date and Time</a>'
                    ),
                    'format' => 'date-time',
                    'example' => '2025-09-02T20:27:02',
                ],
                'projectedAnnualRevenue' => [
                    'type' => ['object', 'null'],
                    'properties' => [
                        'value' => [
                            'type' => 'number',
                            'description' => esc_html__('Projected annual revenue in decimal format', 'give'),
                        ],
                        'valueInMinorUnits' => [
                            'type' => 'integer',
                            'description' => esc_html__('Projected annual revenue in minor units (cents)', 'give'),
                        ],
                        'currency' => [
                            'type' => 'string',
                            'format' => 'text-field',
                            'description' => esc_html__('Currency code (e.g., USD, EUR)', 'give'),
                        ],
                    ],
                    'required' => ['value', 'valueInMinorUnits', 'currency'],
                    'description' => esc_html__('Projected annual revenue for this subscription', 'give'),
                    'readOnly' => true,
                ],
            ],
        ];
    }
}
