<?php

namespace Give\API\REST\V3\Routes\Subscriptions\Fields;

use DateTime;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @since 4.8.0
 */
class SubscriptionFields
{
    /**
     * Process field values for special data types before setting them on the subscription model.
     *
     * @since 4.8.0
     */
    public static function processValue(string $key, $value)
    {
        switch ($key) {
            case 'amount':
            case 'feeAmountRecovered':
                if (is_array($value)) {
                    // Handle Money object array format: ['value' => 100.00, 'currency' => 'USD']
                    if (isset($value['value']) && isset($value['currency'])) {
                        return Money::fromDecimal($value['value'], $value['currency']);
                    }
                }

                return $value;

            case 'status':
                if (is_string($value) && SubscriptionStatus::isValid($value)) {
                    return new SubscriptionStatus($value);
                }

                return $value;

            case 'period':
                if (is_string($value)) {
                    return new SubscriptionPeriod($value);
                }

                return $value;

            case 'mode':
                if (is_string($value) && SubscriptionMode::isValid($value)) {
                    return new SubscriptionMode($value);
                }

                return $value;

            case 'gatewayId':
                // Gateway ID is a simple string, no special processing needed
                return $value;

            case 'createdAt':
            case 'renewsAt':
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
