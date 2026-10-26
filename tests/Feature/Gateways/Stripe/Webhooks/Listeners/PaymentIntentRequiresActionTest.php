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
 * @since TBD
 */
class PaymentIntentRequiresActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     *
     * @test
     */
    public function itRecordsMicrodepositVerificationAndEmailsTheDonor(): void
    {
        $hostedVerificationUrl = 'https://payments.stripe.com/microdeposit/test';

        $donation = Donation::factory()->create([
            'type' => DonationType::SINGLE(),
            'status' => DonationStatus::PROCESSING(),
        ]);
        $donation->gatewayId = StripePaymentElementGateway::id();
        $donation->gatewayTransactionId = 'stripe-payment-intent-id';
        $donation->save();

        $emails = [];
        add_filter('pre_wp_mail', static function ($return, $atts) use (&$emails) {
            $emails[] = $atts;

            return true;
        }, 10, 2);

        $event = Event::constructFrom([
            'data' => [
                'object' => PaymentIntent::constructFrom([
                    'id' => $donation->gatewayTransactionId,
                    'amount' => $donation->amount->formatToMinorAmount(),
                    'currency' => $donation->amount->getCurrency()->getCode(),
                    'client_secret' => 'client-secret',
                    'status' => 'requires_action',
                    'next_action' => [
                        'type' => 'verify_with_microdeposits',
                        'verify_with_microdeposits' => [
                            'hosted_verification_url' => $hostedVerificationUrl,
                        ],
                    ],
                ]),
            ],
        ]);

        (new PaymentIntentRequiresAction)($event);

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
     * @since TBD
     *
     * @test
     */
    public function itIgnoresOtherNextActions(): void
    {
        $donation = Donation::factory()->create([
            'type' => DonationType::SINGLE(),
            'status' => DonationStatus::PROCESSING(),
        ]);
        $donation->gatewayId = StripePaymentElementGateway::id();
        $donation->gatewayTransactionId = 'stripe-payment-intent-id';
        $donation->save();

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

        (new PaymentIntentRequiresAction)($event);

        self::assertEmpty(give_get_meta($donation->id, RecordMicrodepositVerification::META_KEY, true));
    }
}
