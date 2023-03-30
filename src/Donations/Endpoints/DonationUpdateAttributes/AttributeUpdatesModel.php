<?php

namespace Give\Donations\Endpoints\DonationUpdateAttributes;

use Give\Donations\Models\Donation;

interface AttributeUpdatesModel
{
    /**
     * @param          $value
     * @param Donation $donation
     *
     * @return void
     */
    public static function update($value, Donation $donation);
}
