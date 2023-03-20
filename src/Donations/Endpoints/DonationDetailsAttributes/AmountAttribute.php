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
class AmountAttribute extends DonationDetailsAttribute
{
    /**
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'amount';
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
                        'invalid_donation_amount',
                        __('Invalid Donation amount.', 'give'),
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
