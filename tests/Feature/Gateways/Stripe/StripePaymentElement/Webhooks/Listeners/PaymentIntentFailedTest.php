<?php

namespace Give\Tests\Feature\Gateways\Stripe\StripePaymentElement\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners\PaymentIntentPaymentFailed;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\Feature\Gateways\Stripe\TestTraits\HasMockStripeAccounts;
use Give\Tests\Feature\Gateways\Stripe\TestTraits\HasStripePaymentElementSubscriptions;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Stripe\Event;
use Stripe\PaymentIntent;

class PaymentIntentFailedTest extends TestCase
{
    use HasMockStripeAccounts;
    use HasStripePaymentElementSubscriptions;
    use RefreshDatabase;

    /**
     * @since 3.0.0
     *
     * @throws Exception
     */
    public function testShouldUpdateDonationStatus()
    {
        $donation = Donation::factory()->create([
            'type' => DonationType::SINGLE(),
            'status' => DonationStatus::PROCESSING(),
        ]);

        $donation->gatewayId = StripePaymentElementGateway::id();
        $donation->gatewayTransactionId = 'stripe-payment-intent-id';
        $donation->save();

        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => PaymentIntent::constructFrom([
                    'id' => $donation->gatewayTransactionId,
                    'amount' => $donation->amount->formatToMinorAmount(),
                    'currency' => $donation->amount->getCurrency()->getCode(),
                    'client_secret' => 'client-secret',
                    'status' => 'failed'
                ])
            ]
        ]);

        $listener = new PaymentIntentPaymentFailed();

        $listener->processEvent($mockEvent);

        // Refresh donation model
        $donation = Donation::find($donation->id);

        $this->assertTrue($donation->status->isFailed());
    }

    /**
     * @since 4.20.0
     *
     * @throws Exception
     */
    public function testShouldFailInitialSubscriptionDonationAndMoveSubscriptionToPending()
    {
        $subscription = $this->createStripePaymentElementSubscription(
            SubscriptionStatus::ACTIVE(),
            DonationStatus::PROCESSING()
        );

        (new PaymentIntentPaymentFailed())->processEvent(
            $this->createPaymentFailedEvent($subscription->initialDonation())
        );

        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->initialDonation()->status->isFailed());
        $this->assertTrue($subscription->status->isPending());
    }

    /**
     * @since 4.20.0
     *
     * @throws Exception
     */
    public function testShouldNotChangeCompletedInitialSubscriptionDonation()
    {
        $subscription = $this->createStripePaymentElementSubscription(
            SubscriptionStatus::ACTIVE(),
            DonationStatus::COMPLETE()
        );

        (new PaymentIntentPaymentFailed())->processEvent(
            $this->createPaymentFailedEvent($subscription->initialDonation())
        );

        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->initialDonation()->status->isComplete());
        $this->assertTrue($subscription->status->isActive());
    }

    /**
     * An earlier failure event failed the donation but could not save the subscription; this event
     * finishes the change without adding a second donation note.
     *
     * @since 4.20.0
     *
     * @throws Exception
     */
    public function testShouldMoveActiveSubscriptionToPendingWhenInitialDonationAlreadyFailed()
    {
        $subscription = $this->createStripePaymentElementSubscription(
            SubscriptionStatus::ACTIVE(),
            DonationStatus::FAILED()
        );
        $expectedNoteCount = $subscription->initialDonation()->notes()->count();

        (new PaymentIntentPaymentFailed())->processEvent(
            $this->createPaymentFailedEvent($subscription->initialDonation())
        );

        $subscription = Subscription::find($subscription->id);

        $this->assertTrue($subscription->status->isPending());
        $this->assertTrue($subscription->initialDonation()->status->isFailed());
        $this->assertSame($expectedNoteCount, $subscription->initialDonation()->notes()->count());
    }

    /**
     * @since 4.20.0
     */
    private function createPaymentFailedEvent(Donation $donation): Event
    {
        return Event::constructFrom([
            'data' => [
                'object' => PaymentIntent::constructFrom([
                    'id' => $donation->gatewayTransactionId,
                    'amount' => $donation->amount->formatToMinorAmount(),
                    'currency' => $donation->amount->getCurrency()->getCode(),
                    'client_secret' => 'client-secret',
                    'status' => 'requires_payment_method',
                ]),
            ],
        ]);
    }
}
