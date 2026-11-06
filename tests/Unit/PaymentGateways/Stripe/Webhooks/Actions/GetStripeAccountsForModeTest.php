<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Actions;

use Give\PaymentGateways\Stripe\Webhooks\Actions\GetStripeAccountsForMode;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;

/**
 * @since TBD
 */
final class GetStripeAccountsForModeTest extends TestCase
{
    use UsesFakeStripeApi;

    /**
     * @since TBD
     */
    public function testItSkipsSavedEntriesThatAreNotValidAccounts(): void
    {
        $account = $this->makeAccount();
        $missingFields = $account->toArray();
        unset($missingFields['account_id']);

        give_update_option('_give_stripe_get_all_accounts', [
            'not_an_array' => 'acct_string',
            'missing_fields' => $missingFields,
            $account->accountSlug => $account->toArray(),
        ]);

        $accounts = (new GetStripeAccountsForMode())('test');

        $this->assertSame([$account->accountSlug], array_keys($accounts));
        $this->assertSame($account->testSecretKey, $accounts[$account->accountSlug]->testSecretKey);
    }

    /**
     * @since TBD
     */
    public function testItReturnsAManualAccountOnlyForTheModeItHasAKeyFor(): void
    {
        $liveOnly = $this->makeAccount([
            'type' => 'manual',
            'account_slug' => 'acct_live_only',
            'test_secret_key' => '',
            'test_publishable_key' => '',
        ]);
        $testOnly = $this->makeAccount([
            'type' => 'manual',
            'account_slug' => 'acct_test_only',
            'live_secret_key' => '',
            'live_publishable_key' => '',
        ]);

        give_update_option('_give_stripe_get_all_accounts', [
            $liveOnly->accountSlug => $liveOnly->toArray(),
            $testOnly->accountSlug => $testOnly->toArray(),
        ]);

        $this->assertSame([$liveOnly->accountSlug], array_keys((new GetStripeAccountsForMode())('live')));
        $this->assertSame([$testOnly->accountSlug], array_keys((new GetStripeAccountsForMode())('test')));
    }

    /**
     * @since TBD
     */
    public function testItKeysTheAccountsBySlug(): void
    {
        $first = $this->makeAccount(['account_slug' => 'acct_first', 'account_name' => 'First']);
        $second = $this->makeAccount(['account_slug' => 'acct_second', 'account_name' => 'Second']);

        give_update_option('_give_stripe_get_all_accounts', [
            $first->accountSlug => $first->toArray(),
            $second->accountSlug => $second->toArray(),
        ]);

        $accounts = (new GetStripeAccountsForMode())('test');

        $this->assertSame([$first->accountSlug, $second->accountSlug], array_keys($accounts));
        $this->assertSame($first->accountName, $accounts[$first->accountSlug]->accountName);
        $this->assertSame($second->accountName, $accounts[$second->accountSlug]->accountName);
    }

    /**
     * @since TBD
     */
    public function testItReturnsNothingWhenNoAccountIsSaved(): void
    {
        give_delete_option('_give_stripe_get_all_accounts');

        $this->assertSame([], (new GetStripeAccountsForMode())('live'));
    }
}
