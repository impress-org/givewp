<?php

/**
 * @since 4.20.0
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Actions;

use Exception;
use Give\PaymentGateways\Stripe\Models\AccountDetail;

/**
 * Lists the saved Stripe accounts, Connect or manual, that hold a key for the given mode.
 *
 * @since 4.20.0
 */
final class GetStripeAccountsForMode
{
    /**
     * @since 4.20.0
     *
     * @param string $mode "live" or "test"
     *
     * @return array<string, AccountDetail> keyed by account slug
     */
    public function __invoke(string $mode): array
    {
        $accounts = [];

        foreach ((array)give_stripe_get_all_accounts() as $slug => $account) {
            if (!is_array($account)) {
                continue;
            }

            try {
                $account = AccountDetail::fromArray($account);
            } catch (Exception $exception) {
                continue;
            }

            /* A manual account from the give-stripe add-on can hold keys for one mode only. */
            if ('live' === $mode ? $account->liveSecretKey : $account->testSecretKey) {
                $accounts[$slug] = $account;
            }
        }

        return $accounts;
    }
}
