<?php

namespace Give\Donations\Endpoints\DonationUpdateAttributes;

use Give\Donations\Models\Donation;
use Give\Framework\Support\ValueObjects\Money;

/**
 * Class FeeAmountRecoveredAttribute
 *
 * @unreleased
 */
class FeeAmountRecoveredAttribute extends DonationUpdateAttribute
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
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => 'rest_validate_request_arg',
        ];
    }

    /**
     * @inheritDoc
     */
    public static function update($value, Donation $donation)
    {
        $donation->feeAmountRecovered = Money::fromDecimal(
            $value,
            $donation->feeAmountRecovered
                ? $donation->feeAmountRecovered->getCurrency()
                : $donation->amount->getCurrency()
        );
    }
}
