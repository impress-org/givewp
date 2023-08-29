<?php

namespace Give\Tests\Feature\Gateways\Stripe\TestTraits;

trait HasMockStripeAccounts {
    /**
     * @since 3.0.0
     */
    public function addMockStripeAccounts()
    {
        $accounts = [
            'account_1' => [
                'type' => 'connect',
                'live_secret_key' => 'sk_live_xxxxxxxx',
                'live_publishable_key' => 'pk_live_xxxxxxxx',
                'test_secret_key' => 'sk_test_xxxxxxxx',
                'test_publishable_key' => 'pk_test_xxxxxxxx',
            ],
            'account_2' => [
                'type' => 'manual',
                'live_secret_key' => 'sk_live_xxxxxxxx',
                'live_publishable_key' => 'pk_live_xxxxxxxx',
                'test_secret_key' => 'sk_test_xxxxxxxx',
                'test_publishable_key' => 'pk_test_xxxxxxxx',
            ],
        ];
        give_update_option('_give_stripe_get_all_accounts', $accounts);

        // Set dummy default key.
        give_update_option('_give_stripe_default_account', 'account_2');
    }
}
