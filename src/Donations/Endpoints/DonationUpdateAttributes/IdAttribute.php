<?php

namespace Give\Donations\Endpoints\DonationUpdateAttributes;

use WP_Error;

/**
 * Class IdAttribute
 *
 * @unreleased
 */
class IdAttribute extends DonationUpdateAttribute
{
    /**
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'id';
    }

    /**
     * @inheritDoc
     */
    public static function getDefinition(): array
    {
        return [
            'type' => 'integer',
            'required' => true,
            'minimum' => 1,
            'sanitize_callback' => 'absint',
            'validate_callback' => function ($param) {
                if (give()->donations->getById($param) === null) {
                    return new WP_Error(
                        'donation_not_found',
                        __('Donation not found.', 'give'),
                        ['status' => 404]
                    );
                }

                return true;
            },
        ];
    }
}
