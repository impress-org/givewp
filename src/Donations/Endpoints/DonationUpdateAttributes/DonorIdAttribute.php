<?php

namespace Give\Donations\Endpoints\DonationUpdateAttributes;

use Give\Donations\Endpoints\DonationDetailsAttributes\DonationDetailsAttribute;
use Give\Donations\Endpoints\UpdateDonationAttributes\UpdateDonationAttribute;
use Give\Donations\Models\Donation;
use WP_Error;

/**
 * Class DonorIdAttribute
 *
 * @unreleased
 */
class DonorIdAttribute extends DonationUpdateAttribute implements AttributeUpdatesModel
{
    /**
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'donorId';
    }

    /**
     * @inheritDoc
     */
    public static function getDefinition(): array
    {
        return [
            'type' => 'integer',
            'required' => false,
            'minimum' => 1,
            'sanitize_callback' => function ($param) {
                return absint(intval($param));
            },
            'validate_callback' => function ($param) {
                if (give()->donors->getById($param) === null) {
                    return new WP_Error(
                        'donor_not_found',
                        __('Donor not found.', 'give'),
                        ['status' => 404]
                    );
                }

                return true;
            },
        ];
    }

    /**
     * @inheritDoc
     */
    public static function update($value, Donation $donation)
    {
        $donor = give()->donors->getById($value);

        $donation->donorId = $value;
        $donation->firstName = $donor->firstName;
        $donation->lastName = $donor->lastName;
        $donation->email = $donor->email;
    }
}
