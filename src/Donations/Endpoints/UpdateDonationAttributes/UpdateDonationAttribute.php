<?php

namespace Give\Donations\Endpoints\UpdateDonationAttributes;

use Give\Donations\Models\Donation;

abstract class UpdateDonationAttribute
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
