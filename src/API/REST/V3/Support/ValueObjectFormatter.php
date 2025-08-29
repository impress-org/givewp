<?php

namespace Give\API\REST\V3\Support;

/**
 * @unreleased
 */
class ValueObjectFormatter
{
    /**
     * @unreleased
     */
    public static function formatValueObjects(array $data): array
    {
        // Format SubscriptionPeriod to string
        if (isset($data['period']) && method_exists($data['period'], 'getValue')) {
            $data['period'] = $data['period']->getValue();
        }

        // Format SubscriptionStatus to string
        if (isset($data['status']) && method_exists($data['status'], 'getValue')) {
            $data['status'] = $data['status']->getValue();
        }

        // Format SubscriptionMode to string
        if (isset($data['mode']) && method_exists($data['mode'], 'getValue')) {
            $data['mode'] = $data['mode']->getValue();
        }

        return $data;
    }
}
