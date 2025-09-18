<?php

namespace Give\API\REST\V3\Routes\Donations\Fields;

use DateTime;
use Give\Donations\Properties\BillingAddress;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @since 4.8.0
 */
class DonationFields
{
    /**
     * Process field values for special data types before setting them on the donation model.
     *
     * @since 4.8.0
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public static function processValue(string $key, $value)
    {
        switch ($key) {
            case 'amount':
            case 'feeAmountRecovered':
                if (is_array($value)) {
                    // Handle Money object array format: ['amount' => 100.00, 'currency' => 'USD']
                    if (isset($value['amount']) && isset($value['currency'])) {
                        return Money::fromDecimal($value['amount'], $value['currency']);
                    }
                }
                return $value;

            case 'status':
                if (is_string($value) && DonationStatus::isValid($value)) {
                    return new DonationStatus($value);
                }
                return $value;

            case 'type':
                if (is_string($value) && DonationType::isValid($value)) {
                    return new DonationType($value);
                }
                return $value;

            case 'mode':
                if (is_string($value) && DonationMode::isValid($value)) {
                    return new DonationMode($value);
                }
                return $value;

            case 'billingAddress':
                if (is_array($value)) {
                    return BillingAddress::fromArray($value);
                }
                return $value;

            case 'createdAt':
            case 'updatedAt':
                try {
                    if (is_string($value)) {
                        return new DateTime($value, wp_timezone());
                    } elseif (is_array($value)) {
                        return new DateTime($value['date'], new \DateTimeZone($value['timezone']));
                    }
                } catch (\Exception $e) {
                    throw new InvalidArgumentException("Invalid date format for {$key}: {$value}.");
                }
                return $value;

            default:
                return $value;
        }
    }
}
