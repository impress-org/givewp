<?php

namespace Give\API\REST\V3\Routes\Donors\Actions;

/**
 * @unreleased
 */
class GetDonorItemSchema
{
    /**
     * @unreleased
     */
    public function __invoke(): array
    {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'givewp/donor',
            'description' => esc_html__('Donor routes for CRUD operations', 'give'),
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => esc_html__('Donor ID', 'give'),
                    'readonly' => true,
                ],
                'prefix' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Donor prefix', 'give'),
                    'format' => 'text-field',
                ],
                'firstName' => [
                    'type' => 'string',
                    'description' => esc_html__('Donor first name', 'give'),
                    'minLength' => 1,
                    'maxLength' => 128,
                    'errorMessage' => esc_html__('First name is required', 'give'),
                    'format' => 'text-field',
                    'required' => true,
                ],
                'lastName' => [
                    'type' => 'string',
                    'description' => esc_html__('Donor last name', 'give'),
                    'minLength' => 1,
                    'maxLength' => 128,
                    'errorMessage' => esc_html__('Last name is required', 'give'),
                    'format' => 'text-field',
                    'required' => true,
                ],
                'email' => [
                    'type' => 'string',
                    'description' => esc_html__('Donor email', 'give'),
                    'format' => 'email',
                    'required' => true,
                ],
                'additionalEmails' => [
                    'type' => 'array',
                    'description' => esc_html__('Donor additional emails', 'give'),
                    'items' => [
                        'type' => 'string',
                        'format' => 'email',
                    ],
                ],
                'phone' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Donor phone', 'give'),
                    'pattern' => '^$|^[\+]?[1-9][\d\s\-\(\)]{7,20}$',
                ],
                'company' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Donor company', 'give'),
                    'format' => 'text-field',
                ],
                'avatarId' => [
                    'type' => ['integer', 'string', 'null'],
                    'description' => esc_html__('Donor avatar ID', 'give'),
                    'pattern' => '^$|^[0-9]+$',
                    'errorMessage' => esc_html__('Invalid avatar ID', 'give'),
                ],
                'addresses' => [
                    'type' => 'array',
                    'description' => esc_html__('Donor addresses', 'give'),
                    'items' => [
                        'type' => 'object',
                        'description' => esc_html__('Donor address', 'give'),
                        'properties' => [
                            'address1' => [
                                'type' => 'string',
                                'description' => esc_html__('Donor address line 1', 'give'),
                                'format' => 'text-field',
                            ],
                            'address2' => [
                                'type' => 'string',
                                'description' => esc_html__('Donor address line 2', 'give'),
                                'format' => 'text-field',
                            ],
                            'city' => [
                                'type' => 'string',
                                'description' => esc_html__('Donor address city', 'give'),
                                'format' => 'text-field',
                            ],
                            'state' => [
                                'type' => 'string',
                                'description' => esc_html__('Donor address state', 'give'),
                                'format' => 'text-field',
                            ],
                            'country' => [
                                'type' => 'string',
                                'description' => esc_html__('Donor address country', 'give'),
                                'format' => 'text-field',
                            ],
                            'zip' => [
                                'type' => 'string',
                                'description' => esc_html__('Donor address zip', 'give'),
                                'format' => 'text-field',
                            ],
                        ],
                    ],
                ],
                'customFields' => [
                    'type' => 'array',
                    'readonly' => true,
                    'description' => esc_html__('Custom fields (sensitive data)', 'give'),
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'label' => [
                                'type' => 'string',
                                'description' => esc_html__('Field label', 'give'),
                                'format' => 'text-field',
                            ],
                            'value' => [
                                'type' => 'string',
                                'description' => esc_html__('Field value', 'give'),
                                'format' => 'text-field',
                            ],
                        ],
                    ],
                ],
                'createdAt' => [
                    'type' => ['string', 'null'],
                    'description' => sprintf(
                        /* translators: %s: WordPress documentation URL */
                        esc_html__('Donor creation date in ISO 8601 format. Follows WordPress REST API date format standards. See %s for more information.', 'give'),
                        '<a href="https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#format" target="_blank">WordPress REST API Date and Time</a>'
                    ),
                    'format' => 'date-time',
                    'example' => '2025-09-02T20:27:02',
                    'readonly' => true,
                ],
                'userId' => [
                    'type' => ['integer', 'null'],
                    'description' => esc_html__('WordPress user ID associated with the donor', 'give'),
                    'readonly' => true,
                ],
                'name' => [
                    'type' => 'string',
                    'description' => esc_html__('Donor full name (calculated from firstName and lastName)', 'give'),
                    'readonly' => true,
                ],
                'avatarUrl' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('URL of the donor avatar image', 'give'),
                    'format' => 'uri',
                    'readonly' => true,
                ],
                'wpUserPermalink' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Link to edit the WordPress user associated with the donor', 'give'),
                    'format' => 'uri',
                    'readonly' => true,
                ],
                'totalAmountDonated' => [
                    'type' => 'object',
                    'properties' => [
                        'value' => [
                            'type' => 'number',
                            'description' => esc_html__('Total amount donated in decimal format', 'give'),
                        ],
                        'valueInMinorUnits' => [
                            'type' => 'integer',
                            'description' => esc_html__('Total amount donated in minor units (cents)', 'give'),
                        ],
                        'currency' => [
                            'type' => 'string',
                            'format' => 'text-field',
                            'description' => esc_html__('Currency code (e.g., USD, EUR)', 'give'),
                        ],
                    ],
                    'description' => esc_html__('Total amount donated by the donor', 'give'),
                    'readonly' => true,
                ],
                'totalNumberOfDonations' => [
                    'type' => 'integer',
                    'description' => esc_html__('Total number of donations made by the donor', 'give'),
                    'readonly' => true,
                ],
            ],
        ];
    }
}
