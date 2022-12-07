<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Actions;

use Give\Helpers\Call;
use Give\PaymentGateways\Gateways\Stripe\Actions\UpdateStripeAccountStatementDescriptor;
use Give\PaymentGateways\Stripe\Repositories\Settings;
use Give\Tests\TestCase;

/**
 * @since 2.19.0
 */
class UpdateStripeAccountStatementDescriptorTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->setUpStripeAccounts();
    }

    public function testReturnErrorOnInValidStripeAccount()
    {
        $this->expectExceptionMessage('Invalid Stripe account id.');
        Call::invoke(UpdateStripeAccountStatementDescriptor::class, 'abc', '');
    }

    public function testReturnErrorOAlreadySavedStatementDescriptor()
    {
        $this->assertTrue(Call::invoke(UpdateStripeAccountStatementDescriptor::class, 'account_1', get_bloginfo('name')));
    }

    public function testTrueWhenNewStatementDescriptorAddedToStripeAccount()
    {
        $newStatementDescriptorText = 'New Name';
        $stripeAccountId = 'account_1';
        $updated = Call::invoke(
            UpdateStripeAccountStatementDescriptor::class,
            $stripeAccountId,
            $newStatementDescriptorText
        );
        $this->assertTrue($updated);
        $this->assertSame(
            $newStatementDescriptorText,
            give(Settings::class)
                ->getStripeAccountById($stripeAccountId)
                ->statementDescriptor
        );
    }

    private function setUpStripeAccounts()
    {
        give_update_option(
            '_give_stripe_get_all_accounts',
            [
                'account_1' => [
                    'type' => 'manual',
                    'account_name' => 'Account 1',
                    'account_slug' => 'account_1',
                    'account_email' => '',
                    'account_country' => 'BR',
                    'account_id' => 'account_1',
                    'live_secret_key' => 'dummy',
                    'test_secret_key' => 'dummy',
                    'live_publishable_key' => 'dummy',
                    'test_publishable_key' => 'dummy',
                    'statement_descriptor' => get_bloginfo('name'),
                ],
                'account_2' => [
                    'type' => 'manual',
                    'account_name' => 'Account 2',
                    'account_slug' => 'account_2',
                    'account_email' => '',
                    'account_country' => 'US',
                    'account_id' => 'account_2',
                    'live_secret_key' => 'dummy',
                    'test_secret_key' => 'dummy',
                    'live_publishable_key' => 'dummy',
                    'test_publishable_key' => 'dummy',
                    'statement_descriptor' => get_bloginfo('name'),
                ],
            ]
        );
    }
}
