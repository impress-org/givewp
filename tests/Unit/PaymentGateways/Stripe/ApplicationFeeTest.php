<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe;

use Give\PaymentGateways\Stripe\ApplicationFee;
use Give\PaymentGateways\Stripe\Repositories\AccountDetail as AccountDetailRepository;
use Give\Tests\TestCase;

/**
 * Class ApplicationFeeTest
 */
final class ApplicationFeeTest extends TestCase
{

    /**
     * @var AccountDetailRepository
     */
    private $repository;

    /**
     * @var ApplicationFee
     */
    private $gate;

    public function setUp()
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

    public function testCanAddFee()
    {
        give()->singleton(ApplicationFee::class, function () {
            return new ApplicationFee($this->repository->getAccountDetail('account_2'));
        });

        $this->assertTrue(
            ApplicationFee::canAddFee()
        );
    }

    public function testCanNotAddFee()
    {
        give()->singleton(ApplicationFee::class, function () {
            return new ApplicationFee($this->repository->getAccountDetail('account_1'));
        });

        $this->assertFalse(
            ApplicationFee::canAddFee()
        );
    }

    /**
     * @note Run this test first, before GIVE_STRIPE_VERSION is defined in the next test.
     */
    public function testNotIsStripeProAddonActive()
    {
        $this->assertFalse(
            $this->gate->isStripeProAddonActive()
        );
    }

    public function testIsStripeProAddonActive()
    {
        // Mock the Give Stripe Add-on being active.
        define('GIVE_STRIPE_VERSION', '1.2.3');

        $this->assertTrue(
            $this->gate->isStripeProAddonActive()
        );
    }

    public function testIsStripeProAddonInstalled()
    {
        // Mock Stripe Add-on installed.
        $plugins = [
            ['Name' => 'Give - Stripe Gateway'],
        ];

        $this->assertTrue(
            $this->gate->isStripeProAddonInstalled($plugins)
        );
    }

    public function testNotIsStripeProAddonInstalled()
    {
        // Mock no add-ons installed.
        $plugins = [];

        $this->assertFalse(
            $this->gate->isStripeProAddonInstalled($plugins)
        );
    }

    public function testHasLicense()
    {
        // Mock licensing with Stripe Add-on.
        update_option(
            'give_licenses',
            [
                [
                    'is_all_access_pass' => false,
                    'plugin_slug' => 'give-stripe',
                ],
            ]
        );

        $this->assertTrue(
            $this->gate->hasLicense()
        );
    }

    public function testNotHasLicense()
    {
        // Mock licensing without Stripe Add-on.
        update_option(
            'give_licenses',
            [
                [
                    'is_all_access_pass' => false,
                    'plugin_slug' => 'not-stripe-addon',
                ],
            ]
        );

        $this->assertFalse(
            $this->gate->hasLicense()
        );
    }

    public function testHasLicenseAllAccessPass()
    {
        // Mock licensing with All Access pass.
        update_option(
            'give_licenses',
            [
                [
                    'is_all_access_pass' => true,
                    'download' => [
                        [
                            'plugin_slug' => 'give-stripe',
                        ],
                    ],
                ],
            ]
        );

        $this->assertTrue(
            $this->gate->hasLicense()
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
