<?php

namespace Give\Donations\Endpoints\DonationDetailsAttributes;

use DateTime;
use Give\Donations\Models\Donation;
use Give\Framework\Support\Facades\DateTime\Temporal;
use WP_Error;

/**
 * Class IdAttribute
 *
 * @unreleased
 */
class CreatedAtAttribute extends DonationDetailsAttribute
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
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => function ($param) {
                if ( ! DateTime::createFromFormat(Temporal::ISO8601_JS,
                        $param) && ! Temporal::toDateTime($param)) {
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
        $date = DateTime::createFromFormat(Temporal::ISO8601_JS, $value);
        if ( ! $date) {
            $date = Temporal::toDateTime($value);
        }
        $donation->createdAt = $date;

        return $donation;
    }
}
