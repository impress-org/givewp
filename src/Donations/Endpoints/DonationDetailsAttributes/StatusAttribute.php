<?php

namespace Give\Donations\Endpoints\DonationDetailsAttributes;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;

/**
 * Class IdAttribute
 *
 * @unreleased
 */
class StatusAttribute extends DonationDetailsAttribute
{
    /**
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'status';
    }

    /**
     * @inheritDoc
     */
    public static function getDefinition(): array
    {
        return [
            'type' => 'string',
            'required' => false,
            'enum' => array_values(DonationStatus::toArray()),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function update($value, Donation $donation): Donation
    {
        $donation->status = new DonationStatus($value);

        return $donation;
    }
}
