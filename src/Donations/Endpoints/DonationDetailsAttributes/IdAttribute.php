<?php

namespace Give\Donations\Endpoints\DonationDetailsAttributes;

use WP_Error;

/**
 * Class IdAttribute
 *
 * @unreleased
 */
class IdAttribute extends DonationDetailsAttribute
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
