<?php

declare(strict_types=1);

namespace Give\LegacySubscriptions\Actions;

use Give\Subscriptions\ValueObjects\SubscriptionMode;

/**
 * When payment mode was introduced it was possible for users to update core before updating Recurring. In this case,
 * subscriptions could be made which did not have a payment mode. This action ensures that all subscriptions have a
 * payment mode.
 *
 * @since 2.24.0
 */
class EnsureSubscriptionHasPaymentMode
{
    /**
     * Makes sure the payment mode is set when the legacy recurring system is used.
     *
     * @since 2.24.0
     *
     * @param int $id
     * @param array $arguments
     *
     * @return void
     */
    public function __invoke($id, $arguments)
    {
        if (!empty($arguments['payment_mode'])) {
            return;
        }

        give()->subscriptions->updatePaymentMode(
            $id,
            give_is_test_mode() ? SubscriptionMode::TEST() : SubscriptionMode::LIVE()
        );
    }
}
