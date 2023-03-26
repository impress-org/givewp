<?php

namespace Give\Donations\Endpoints\DonationDetailsAttributes\PaymentInformation;

use Give\Donations\Endpoints\DonationDetailsAttributes\UpdateDonationAttribute;
use Give\Donations\Models\Donation;
use Give\Framework\Support\ValueObjects\Money;

/**
 * Class FeeAmountRecoveredAttribute
 *
 * @unreleased
 */
class FeeAmountRecoveredAttribute extends UpdateDonationAttribute
{
    /**
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'feeAmountRecovered';
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
        $donation->feeAmountRecovered = Money::fromDecimal(
            $value,
            $donation->feeAmountRecovered
                ? $donation->feeAmountRecovered->getCurrency()
                : $donation->amount->getCurrency()
        );

        return $donation;
    }
}
