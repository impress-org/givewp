<?php

namespace Give\Donations\Endpoints\DonationUpdateAttributes;

use Give\Donations\Models\Donation;

/**
 * Class CityAttribute
 *
 * @unreleased
 */
class CityAttribute extends DonationUpdateAttribute implements AttributeUpdatesModel
{
    /**
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'city';
    }

    /**
     * @inheritDoc
     */
    public static function getDefinition(): array
    {
        return [
            'type' => 'string',
            'required' => false,
            'sanitize_callback' => 'sanitize_text_field',
        ];
    }

    /**
     * @inheritDoc
     */
    public static function update($value, Donation $donation)
    {
        $donation->billingAddress->city = $value;
    }
}
