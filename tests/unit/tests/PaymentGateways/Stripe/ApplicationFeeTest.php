<?php

use PHPUnit\Framework\TestCase;
use Give\PaymentGateways\Stripe\ApplicationFee;

final class ApplicationFeeTest extends TestCase {

    public function setUp() {
        $this->gate = new ApplicationFee;
    }

    /**
     * @note Run this test first, before GIVE_STRIPE_VERSION is defined in the next test.
     */
    public function testNotIsStripeProAddonActive() {

        $this->assertFalse(
            $this->gate->isStripeProAddonActive()
        );
    }

    public function testIsStripeProAddonActive() {

        // Mock the Give Stripe Add-on being active.
        define( 'GIVE_STRIPE_VERSION', '1.2.3' );

        $this->assertTrue(
            $this->gate->isStripeProAddonActive()
        );
    }

    public function testIsStripeProAddonInstalled() {

        // Mock Stripe Add-on installed.
        $plugins = [
            [ 'Name' => 'Give - Stripe Gateway' ]
        ];

        $this->assertTrue(
            $this->gate->isStripeProAddonInstalled( $plugins )
        );
    }

    public function testNotIsStripeProAddonInstalled() {

        // Mock no add-ons installed.
        $plugins = [];

        $this->assertFalse(
            $this->gate->isStripeProAddonInstalled( $plugins )
        );
    }

    public function testHasLicense() {

        // Mock licensing with Stripe Add-on.
        update_option( 'give_licenses', [[
            'is_all_access_pass' => false,
            'plugin_slug' => 'give-stripe',
        ]]);

        $this->assertTrue(
            $this->gate->hasLicense( 'give-stripe' )
        );
    }

    public function testNotHasLicense() {

        // Mock licensing without Stripe Add-on.
        update_option( 'give_licenses', [[
            'is_all_access_pass' => false,
            'plugin_slug' => 'not-stripe-addon',
        ]]);

        $this->assertFalse(
            $this->gate->hasLicense( 'give-stripe' )
        );
    }

    public function testHasLicenseAllAccessPass() {

        // Mock licensing with All Access pass.
        update_option( 'give_licenses', [[
            'is_all_access_pass' => true,
            'download' => [[
                'plugin_slug' => 'give-stripe',
            ]],
        ]]);

        $this->assertTrue(
            $this->gate->hasLicense( 'give-stripe' )
        );
    }

    public function testConneectedAccontCountrySupportsApplicationFees() {

        give_update_option( '_give_stripe_default_account', 'acct_foo' );
        give_update_option( '_give_stripe_get_all_accounts', [
            'acct_foo' => [
                'account_id' => 'acct_foo',
                'account_country' => 'US'
            ]
        ] );

        $this->assertTrue( $this->gate->doesCountrySupportApplicationFee() );
    }

    public function testConneectedAccontCountryNotSupportsApplicationFees() {

        give_update_option( '_give_stripe_default_account', 'acct_foo' );
        give_update_option( '_give_stripe_get_all_accounts', [
            'acct_foo' => [
                'account_id' => 'acct_foo',
                'account_country' => 'US'
            ]
        ] );

        $this->assertTrue( $this->gate->doesCountrySupportApplicationFee() );
    }
}
