<?php

namespace Give\Donations\Endpoints\DonationUpdateAttributes\PaymentInformation;

use DateTime;
use Give\Donations\Endpoints\DonationUpdateAttributes\DonationUpdateAttribute;
use Give\Donations\Models\Donation;
use Give\Framework\Support\Facades\DateTime\Temporal;
use WP_Error;

/**
 * Class CreatedAtAttribute
 *
 * @unreleased
 */
class CreatedAtAttribute extends DonationUpdateAttribute
{
    /**
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'createdAt';
    }

    /**
     * @inheritDoc
     */
    public static function getDefinition(): array
    {
        return [
            'type' => 'string',
            'required' => false,
            'format' => 'date-time',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => function ($param) {
                if ( ! DateTime::createFromFormat(Temporal::TIMESTAMP, $param)) {
                    return new WP_Error(
                        'invalid_date',
                        __('Invalid date.', 'give'),
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
        $donation->createdAt = DateTime::createFromFormat(Temporal::TIMESTAMP, $value);

        return $donation;
    }
}
