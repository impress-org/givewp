<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Filters;

use Give\Tests\TestCase;

/**
 * @since 2.27.0
 */
class LegacyStripeFiltersTest extends TestCase
{
    /**
     * @since 2.27.0
     */
    public function test_give_stripe_supported_payment_methods_returns_expected_payment_methods()
    {
        add_action('give_stripe_supported_payment_methods', static function ($paymentMethods) {
            $paymentMethods[] = 'next-gen-stripe';

            return $paymentMethods;
        });

        $gateways = give_stripe_supported_payment_methods();

        $this->assertContains('next-gen-stripe', $gateways);
    }
}
