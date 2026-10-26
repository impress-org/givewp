<?php

namespace Give\Tests\Unit\PaymentGateways\Gateways\Stripe;

use Give\Donations\Models\Donation;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Stripe\PaymentIntent;

/**
 * @since TBD
 */
class ProcessAdditionalAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var string|null Location passed to the last intercepted `wp_redirect()` call.
     */
    private $redirectLocation;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!defined('GIVE_UNIT_TESTS')) {
            define('GIVE_UNIT_TESTS', true);
        }

        /*
         * give_send_back_to_checkout() ends in give_die(). Under GIVE_UNIT_TESTS the Give die
         * handler returns instead of exiting, so the test can inspect what happened.
         */
        add_filter('wp_die_handler', '_give_die_handler', 10, 3);

        add_filter('wp_redirect', function ($location) {
            $this->redirectLocation = $location;

            return false;
        });

        give_clear_errors();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        $_POST = [];

        remove_all_filters('wp_redirect');
        give_clear_errors();

        parent::tearDown();
    }

    /**
     * @since TBD
     *
     * @test
     */
    public function itSendsTheDonorBackToTheFormWhenTheRequiredActionHasNoRedirectUrl(): void
    {
        $donation = Donation::factory()->create();
        $formUrl = home_url('/donate/' . wp_generate_password(8, false));
        $_POST['give-current-url'] = $formUrl;

        $paymentIntent = PaymentIntent::constructFrom([
            'id' => 'pi_' . wp_generate_password(12, false),
            'status' => 'requires_action',
            'next_action' => ['type' => 'use_stripe_sdk'],
        ]);

        give_stripe_process_additional_authentication($donation->id, $paymentIntent);

        self::assertArrayHasKey('stripe_error', give_get_errors());
        self::assertStringStartsWith($formUrl, $this->redirectLocation);
        self::assertEmpty(give_get_meta($donation->id, '_give_stripe_payment_intent_require_action_url', true));
    }
}
