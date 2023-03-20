<?php

namespace Give\Donations\Endpoints\DonationDetailsAttributes;

use Give\Donations\Models\Donation;
use Give\Framework\Support\ValueObjects\Money;
use WP_Error;

/**
 * Class IdAttribute
 *
 * @unreleased
 */
class TotalDonationAttribute extends DonationDetailsAttribute
{
    /**
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'totalDonation';
    }

    /**
     * @inheritDoc
     */
    public static function getDefinition(): array
    {
        return [
            'type' => 'number',
            'required' => false,
            'validate_callback' => function ($param) {
                if ($param < 0) {
                    return new WP_Error(
                        'invalid_total_donation',
                        __('Invalid total donation.', 'give'),
                        ['status' => 400]
                    );
                }

                return true;
            },
        ];
    }

    /**
     * @inheritDoc
     */
    public static function update($value, Donation $donation): Donation
    {
        $donation->amount = Money::fromDecimal(
            $value,
            $donation->amount->getCurrency()
        );

        return $donation;
    }
}
