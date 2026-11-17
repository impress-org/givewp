<?php

namespace Give\Tests\Feature\Gateways\Stripe\Webhooks\Listeners;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\PaymentGateways\Gateways\Stripe\Actions\RecordMicrodepositVerification;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners\PaymentIntentRequiresAction;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Stripe\Event;
use Stripe\PaymentIntent;

/**
 * @since 4.19.2
 */
class PaymentIntentRequiresActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.19.2
     *
     * @test
     */
    public function itRecordsMicrodepositVerificationAndEmailsTheDonor(): void
    {
        $hostedVerificationUrl = 'https://payments.stripe.com/microdeposit/test';
        $donation = $this->createDonation();

        $emails = [];
        add_filter('pre_wp_mail', static function ($return, $atts) use (&$emails) {
            $emails[] = $atts;

            return true;
        }, 10, 2);

        (new PaymentIntentRequiresAction)->processEvent(
            $this->createMicrodepositEvent($donation, $hostedVerificationUrl)
        );

        remove_all_filters('pre_wp_mail');

        self::assertSame(
            $hostedVerificationUrl,
            give_get_meta($donation->id, RecordMicrodepositVerification::META_KEY, true)
        );

        $notes = $donation->notes()->getAll();
        self::assertNotEmpty($notes);
        self::assertStringContainsString('microdeposit', strtolower($notes[0]->content));

        self::assertCount(1, $emails);
        self::assertStringContainsString($hostedVerificationUrl, $emails[0]['message']);
    }

    /**
     * @since 4.19.2
     *
     * @test
     */
    public function itIgnoresOtherNextActions(): void
    {
        $donation = $this->createDonation();

        $event = Event::constructFrom([
            'data' => [
                'object' => PaymentIntent::constructFrom([
                    'id' => $donation->gatewayTransactionId,
                    'status' => 'requires_action',
                    'next_action' => [
                        'type' => 'redirect_to_url',
                        'redirect_to_url' => ['url' => 'https://example.com/3ds'],
                    ],
                ]),
            ],
        ]);

        (new PaymentIntentRequiresAction)->processEvent($event);

        self::assertEmpty(give_get_meta($donation->id, RecordMicrodepositVerification::META_KEY, true));
    }

    /**
     * @since 4.19.2
     *
     * @test
     */
    public function itIgnoresAPaymentIntentWhoseAmountDiffersFromTheDonation(): void
    {
        $donation = $this->createDonation();

        (new PaymentIntentRequiresAction)->processEvent(
            $this->createMicrodepositEvent(
                $donation,
                'https://payments.stripe.com/microdeposit/test',
                ['amount' => $donation->amount->formatToMinorAmount() + 1]
            )
        );

        self::assertEmpty(give_get_meta($donation->id, RecordMicrodepositVerification::META_KEY, true));
    }

    /**
     * @since 4.19.2
     *
     * @test
     */
    public function itIgnoresAPaymentIntentWhoseCurrencyDiffersFromTheDonation(): void
    {
        $donation = $this->createDonation();
        $otherCurrency = 'USD' === $donation->amount->getCurrency()->getCode() ? 'eur' : 'usd';

        (new PaymentIntentRequiresAction)->processEvent(
            $this->createMicrodepositEvent(
                $donation,
                'https://payments.stripe.com/microdeposit/test',
                ['currency' => $otherCurrency]
            )
        );

        self::assertEmpty(give_get_meta($donation->id, RecordMicrodepositVerification::META_KEY, true));
    }

    /**
     * @since 4.19.2
     */
    private function createDonation(): Donation
    {
        $donation = Donation::factory()->create([
            'type' => DonationType::SINGLE(),
            'status' => DonationStatus::PROCESSING(),
        ]);
        $donation->gatewayId = StripePaymentElementGateway::id();
        $donation->gatewayTransactionId = 'stripe-payment-intent-id';
        $donation->save();

        return $donation;
    }

    /**
     * Stripe sends the currency in lowercase, so the default here does too.
     *
     * @since 4.19.2
     *
     * @param array{amount?: int, currency?: string} $overrides PaymentIntent fields to replace.
     */
    private function createMicrodepositEvent(Donation $donation, string $hostedVerificationUrl, array $overrides = []): Event
    {
        return Event::constructFrom([
            'data' => [
                'object' => PaymentIntent::constructFrom(array_merge([
                    'id' => $donation->gatewayTransactionId,
                    'amount' => $donation->amount->formatToMinorAmount(),
                    'currency' => strtolower($donation->amount->getCurrency()->getCode()),
                    'client_secret' => 'client-secret',
                    'status' => 'requires_action',
                    'next_action' => [
                        'type' => 'verify_with_microdeposits',
                        'verify_with_microdeposits' => [
                            'hosted_verification_url' => $hostedVerificationUrl,
                        ],
                    ],
                ], $overrides)),
            ],
        ]);
    }
}
