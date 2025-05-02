<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe;

use Give\PaymentGateways\Stripe\ApplicationFee;
use Give\PaymentGateways\Stripe\Repositories\AccountDetail as AccountDetailRepository;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * Class ApplicationFeeTest
 */
final class ApplicationFeeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var AccountDetailRepository
     */
    private $repository;

    /**
     * @var ApplicationFee
     */
    private $gate;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpStripeAccounts();
        $this->repository = new AccountDetailRepository();
        $this->gate = new ApplicationFee($this->repository->getAccountDetail('account_1'));
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

    public function testCanAddFeeIfMerchantCountryIsUS()
    {
        give()->singleton(ApplicationFee::class, function () {
            return new ApplicationFee($this->repository->getAccountDetail('account_2'));
        });

        $this->assertTrue(
            ApplicationFee::canAddFee()
        );
    }

    public function testCanNotAddFeeIfMerchantCountryIsBR()
    {
        give()->singleton(ApplicationFee::class, function () {
            return new ApplicationFee($this->repository->getAccountDetail('account_1'));
        });

        $this->assertFalse(
            ApplicationFee::canAddFee()
        );
    }

    public function testIsCountryNotSupportApplicationFee()
    {
        $this->assertFalse(
            $this->gate->doesCountrySupportApplicationFee()
        );
    }

    public function testIsCountrySupportApplicationFee()
    {
        $applicationFee = new ApplicationFee($this->repository->getAccountDetail('account_2'));
        $this->assertTrue(
            $applicationFee->doesCountrySupportApplicationFee()
        );
    }
}
