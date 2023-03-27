<?php

namespace Give\Donations\Endpoints\DonationUpdateAttributes;

use Give\Donations\Models\Donation;

abstract class DonationUpdateAttribute
{
    /**
     * @return string
     */
    abstract public static function getId(): string;

    /**
     * @return array
     */
    abstract public static function getDefinition(): array;

    /**
     * @param          $value
     * @param Donation $donation
     *
     * @return Donation|bool
     */
    public static function update($value, Donation $donation)
    {
        return false;
    }
}
