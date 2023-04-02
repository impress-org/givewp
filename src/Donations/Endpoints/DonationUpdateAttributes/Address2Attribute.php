<?php

namespace Give\Donations\Endpoints\DonationUpdateAttributes;

use Give\Donations\Models\Donation;

/**
 * Class Address2Attribute
 *
 * @unreleased
 */
class Address2Attribute extends DonationUpdateAttribute implements AttributeUpdatesModel
{
    /**
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'address2';
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
        $donation->billingAddress->address2 = $value;
    }
}
