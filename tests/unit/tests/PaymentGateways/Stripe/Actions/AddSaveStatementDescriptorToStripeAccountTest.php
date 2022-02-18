<?php

use Give\Helpers\Call;
use Give\PaymentGateways\Gateways\Stripe\Actions\AddSaveStatementDescriptorToStripeAccount;
use Give\PaymentGateways\Stripe\Repositories\Settings;
use PHPUnit\Framework\TestCase;

/**
 * @unreleased
 */
class AddSaveStatementDescriptorToStripeAccountTest extends TestCase
{
    protected function setUp()
    {
        $this->setUpStripeAccounts();
    }

    public function testReturnErrorOnInValidStripeAccount()
    {
        $this->expectExceptionMessage('Stripe account id does not match to any saved account ids.');
        Call::invoke(AddSaveStatementDescriptorToStripeAccount::class, 'abc', '');
    }

    public function testReturnErrorOAlreadySavedStatementDescriptor()
    {
        $this->expectExceptionMessage('This Stripe statement descriptor text is already saved in Stripe account.');
        Call::invoke(AddSaveStatementDescriptorToStripeAccount::class, 'account_1', get_bloginfo('name'));
    }

    public function testTrueWhenNewStatementDescriptorAddedToStripeAccount()
    {
        $newStatementDescriptorText = 'New Name';
        $stripeAccountId = 'account_1';
        $updated = Call::invoke(
            AddSaveStatementDescriptorToStripeAccount::class,
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
