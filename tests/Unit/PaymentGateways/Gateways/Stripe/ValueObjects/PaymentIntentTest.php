<?php

namespace Give\Tests\Unit\PaymentGateways\Gateways\Stripe\ValueObjects;

use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentIntent;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class PaymentIntentTest extends TestCase
{
    /**
     * @since TBD
     *
     * @test
     */
    public function nextActionRedirectUrlReturnsUrlWhenStripeRedirects(): void
    {
        $paymentIntent = $this->makePaymentIntent([
            'next_action' => [
                'type' => 'redirect_to_url',
                'redirect_to_url' => ['url' => 'https://example.com/3ds'],
            ],
        ]);

        self::assertSame('https://example.com/3ds', $paymentIntent->nextActionRedirectUrl());
    }

    /**
     * @since TBD
     *
     * @test
     */
    public function nextActionRedirectUrlReturnsNullForMicrodepositVerification(): void
    {
        $paymentIntent = $this->makePaymentIntent([
            'next_action' => [
                'type' => 'verify_with_microdeposits',
                'verify_with_microdeposits' => [
                    'hosted_verification_url' => 'https://payments.stripe.com/microdeposit/test',
                ],
            ],
        ]);

        self::assertNull($paymentIntent->nextActionRedirectUrl());
    }

    /**
     * @since TBD
     *
     * @test
     */
    public function itReadsTheNextActionType(): void
    {
        $paymentIntent = $this->makePaymentIntent([
            'next_action' => ['type' => 'verify_with_microdeposits'],
        ]);

        self::assertSame('verify_with_microdeposits', $paymentIntent->nextActionType());
    }

    /**
     * @since TBD
     *
     * @test
     */
    public function nextActionTypeReturnsNullWhenThereIsNoNextAction(): void
    {
        self::assertNull($this->makePaymentIntent([])->nextActionType());
    }

    /**
     * @since TBD
     *
     * @test
     */
    public function itReadsTheHostedMicrodepositVerificationUrl(): void
    {
        $paymentIntent = $this->makePaymentIntent([
            'next_action' => [
                'type' => 'verify_with_microdeposits',
                'verify_with_microdeposits' => [
                    'hosted_verification_url' => 'https://payments.stripe.com/microdeposit/test',
                ],
            ],
        ]);

        self::assertSame(
            'https://payments.stripe.com/microdeposit/test',
            $paymentIntent->nextActionVerifyWithMicrodepositsUrl()
        );
    }

    /**
     * @since TBD
     *
     * @test
     */
    public function nextActionVerifyWithMicrodepositsUrlReturnsNullForRedirects(): void
    {
        $paymentIntent = $this->makePaymentIntent([
            'next_action' => [
                'type' => 'redirect_to_url',
                'redirect_to_url' => ['url' => 'https://example.com/3ds'],
            ],
        ]);

        self::assertNull($paymentIntent->nextActionVerifyWithMicrodepositsUrl());
    }

    /**
     * Builds a PaymentIntent value object around the given Stripe intent properties.
     *
     * @since TBD
     */
    private function makePaymentIntent(array $properties): PaymentIntent
    {
        $paymentIntent = new PaymentIntent();

        $property = new \ReflectionProperty(PaymentIntent::class, 'paymentIntentObject');
        $property->setAccessible(true);
        $property->setValue($paymentIntent, json_decode(json_encode($properties)));

        return $paymentIntent;
    }
}
