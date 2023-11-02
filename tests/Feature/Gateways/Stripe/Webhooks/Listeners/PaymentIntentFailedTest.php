<?php

namespace Give\Tests\Feature\Gateways\Stripe\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\PaymentGateways\Gateways\Stripe\CreditCardGateway;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners\PaymentIntentPaymentFailed;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Stripe\Event;
use Stripe\PaymentIntent;

class PaymentIntentFailedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.1.0
     *
     * @throws Exception
     */
    public function testShouldUpdateDonationStatus(): void
    {
        $donation = Donation::factory()->create([
            'type' => DonationType::SINGLE(),
            'status' => DonationStatus::PROCESSING(),
        ]);

        $donation->gatewayId = CreditCardGateway::id();
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
}
