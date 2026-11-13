<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\StripePaymentElementGateway\Actions;

use Faker\Factory;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Actions\EnqueueStripeFormBuilderScripts;
use Give\Tests\TestCase;

class EnqueueStripeFormBuilderScriptsTest extends TestCase
{
    private const HANDLE = 'givewp-stripe-payment-element-form-builder';

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        wp_dequeue_script(self::HANDLE);
        wp_deregister_script(self::HANDLE);
        wp_dequeue_style(self::HANDLE);
        wp_deregister_style(self::HANDLE);

        give_delete_option('gateways_v3');
        give_delete_option('_give_stripe_get_all_accounts');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testLocalizedAccountsContainOnlyAccountIdAndName()
    {
        $accounts = $this->seedStripeAccounts();
        give_update_option('gateways_v3', ['stripe_payment_element' => ['enabled' => true]]);

        (new EnqueueStripeFormBuilderScripts())();

        $localizedData = wp_scripts()->get_data(self::HANDLE, 'data');

        $expected = [];
        foreach ($accounts as $slug => $account) {
            $expected[$slug] = [
                'account_id' => $account['account_id'],
                'account_name' => $account['account_name'],
            ];
        }

        $this->assertSame($expected, $this->extractAllAccounts($localizedData));
    }

    /**
     * @since TBD
     */
    public function testLocalizedDataDoesNotContainStoredAccountCredentials()
    {
        $accounts = $this->seedStripeAccounts();
        give_update_option('gateways_v3', ['stripe_payment_element' => ['enabled' => true]]);

        (new EnqueueStripeFormBuilderScripts())();

        $localizedData = wp_scripts()->get_data(self::HANDLE, 'data');

        foreach ($accounts as $account) {
            foreach (['live_secret_key', 'test_secret_key', 'live_publishable_key', 'test_publishable_key'] as $field) {
                $this->assertStringNotContainsString($field, $localizedData);
                $this->assertStringNotContainsString($account[$field], $localizedData);
            }
        }
    }

    /**
     * @since TBD
     */
    public function testNothingIsLocalizedWhenStripePaymentElementIsDisabled()
    {
        $this->seedStripeAccounts();
        give_update_option('gateways_v3', []);

        (new EnqueueStripeFormBuilderScripts())();

        $this->assertFalse(wp_script_is(self::HANDLE, 'enqueued'));
    }

    /**
     * @since TBD
     *
     * @return array<string, array<string, string>>
     */
    private function seedStripeAccounts(): array
    {
        $faker = Factory::create();
        $accounts = [];

        for ($i = 1; $i <= 3; $i++) {
            $accountId = 'acct_' . $faker->regexify('[A-Za-z0-9]{15}');

            $accounts[$accountId] = [
                'type' => 'connect',
                'account_id' => $accountId,
                'account_slug' => $accountId,
                'account_name' => $faker->company(),
                'account_country' => $faker->countryCode(),
                'account_email' => $faker->email(),
                'live_secret_key' => 'sk_live_' . $faker->regexify('[A-Za-z0-9]{40}'),
                'test_secret_key' => 'sk_test_' . $faker->regexify('[A-Za-z0-9]{40}'),
                'live_publishable_key' => 'pk_live_' . $faker->regexify('[A-Za-z0-9]{40}'),
                'test_publishable_key' => 'pk_test_' . $faker->regexify('[A-Za-z0-9]{40}'),
            ];
        }

        give_update_option('_give_stripe_get_all_accounts', $accounts);
        give_update_option('_give_stripe_default_account', current(array_keys($accounts)));

        return $accounts;
    }

    /**
     * @since TBD
     */
    private function extractAllAccounts(string $localizedData): array
    {
        preg_match('/var stripePaymentElementGatewaySettings = (\{.*\});/s', $localizedData, $matches);

        return json_decode($matches[1], true)['allAccounts'];
    }
}
