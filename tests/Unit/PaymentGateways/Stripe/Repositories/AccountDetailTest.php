<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Repositories;

use Give\PaymentGateways\Exceptions\InvalidPropertyName;
use Give\PaymentGateways\Stripe\Repositories\AccountDetail as AccountDetailRepository;
use Give_Donate_Form;
use Give_Helper_Form;
use Give\Tests\TestCase;

class AccountDetailTest extends TestCase
{
    /**
     * @var AccountDetailRepository
     */
    private $repository;

    /**
     * @var Give_Donate_Form
     */
    private $form;

    public function setUp()
    {
        $this->repository = new AccountDetailRepository();
        $this->form = Give_Helper_Form::create_simple_form();
    }

    public function testDonationFormUseGlobalDefaultStripeAccount()
    {
        $globalStripeAccountId = 'account_1';

        give_update_option(
            '_give_stripe_get_all_accounts',
            [
                $globalStripeAccountId => [
                    'type' => 'manual',
                    'account_name' => 'Account 1',
                    'account_slug' => $globalStripeAccountId,
                    'account_email' => '',
                    'account_country' => '',
                    'account_id' => $globalStripeAccountId,
                    'live_secret_key' => 'dummy',
                    'test_secret_key' => 'dummy',
                    'live_publishable_key' => 'dummy',
                    'test_publishable_key' => 'dummy',
                    'statement_descriptor' => get_bloginfo('name')
                ]
            ]
        );
        give_update_option('_give_stripe_default_account', $globalStripeAccountId);
        give_update_meta($this->form->get_ID(), 'give_stripe_per_form_accounts', 'disabled');

        $this->assertSame(
            $globalStripeAccountId,
            $this->repository->getDonationFormStripeAccountId($this->form->get_ID())->accountId
        );
    }

    public function testDonationFormUseManuallySelectedStripeAccount()
    {
        $globalStripeAccountId = 'account_1';
        $manuallySelectedStripeAccountId = 'account_2';

        give_update_option(
            '_give_stripe_get_all_accounts',
            [
                $manuallySelectedStripeAccountId => [
                    'type' => 'manual',
                    'account_name' => 'Account 1',
                    'account_slug' => $manuallySelectedStripeAccountId,
                    'account_email' => '',
                    'account_country' => '',
                    'account_id' => $manuallySelectedStripeAccountId,
                    'live_secret_key' => 'dummy',
                    'test_secret_key' => 'dummy',
                    'live_publishable_key' => 'dummy',
                    'test_publishable_key' => 'dummy',
                    'statement_descriptor' => get_bloginfo('name')
                ]
            ]
        );
        give_update_option('_give_stripe_default_account', $globalStripeAccountId);
        give_update_meta($this->form->get_ID(), 'give_stripe_per_form_accounts', 'enabled');
        give_update_meta($this->form->get_ID(), '_give_stripe_default_account', $manuallySelectedStripeAccountId);

        $this->assertSame(
            $manuallySelectedStripeAccountId,
            $this->repository->getDonationFormStripeAccountId($this->form->get_ID())->accountId
        );
    }

    public function testValidStripeAccountId()
    {
        $accountId = 'account_1';
        give_update_option(
            '_give_stripe_get_all_accounts',
            [
                $accountId => [
                    'type' => 'manual',
                    'account_name' => 'Account 1',
                    'account_slug' => $accountId,
                    'account_email' => '',
                    'account_country' => '',
                    'account_id' => $accountId,
                    'live_secret_key' => 'dummy',
                    'test_secret_key' => 'dummy',
                    'live_publishable_key' => 'dummy',
                    'test_publishable_key' => 'dummy',
                    'statement_descriptor' => get_bloginfo('name')
                ]
            ]
        );

        $account = $this->repository->getAccountDetail($accountId);
        $this->assertSame($accountId, $account->accountId);
    }

    public function testNotValidStripeAccountId()
    {
        $accountId = 'account_1';
        give_update_option(
            '_give_stripe_get_all_accounts',
            [
                $accountId => [
                    'type' => 'manual',
                    'account_name' => 'Account 1',
                    'account_slug' => $accountId,
                    'account_email' => '',
                    'account_country' => '',
                    'account_id' => $accountId,
                    'live_secret_key' => 'dummy',
                    'test_secret_key' => 'dummy',
                    'live_publishable_key' => 'dummy',
                    'test_publishable_key' => 'dummy',
                    'statement_descriptor' => get_bloginfo('name')
                ]
            ]
        );

        $this->expectException(InvalidPropertyName::class);
        $this->repository->getAccountDetail('account_2');
    }
}
