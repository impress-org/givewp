<?php

namespace Give\Donations\Endpoints\UpdateDonationAttributes\PaymentInformation;

use Give\Donations\Endpoints\UpdateDonationAttributes\UpdateDonationAttribute;
use Give\Donations\Models\Donation;
use Give\Framework\Support\ValueObjects\Money;

/**
 * Class AmountAttribute
 *
 * @unreleased
 */
class AmountAttribute extends UpdateDonationAttribute
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
            'minimum' => 0,
            'sanitize_callback' => function ($param) {
                return floatval($param);
            },
            'validate_callback' => 'rest_validate_request_arg',
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
