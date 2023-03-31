<?php

namespace Give\Tests\Feature\Gateways\Stripe\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\NextGen\Gateways\Stripe\NextGenStripeGateway\NextGenStripeGateway;
use Give\NextGen\Gateways\Stripe\NextGenStripeGateway\Webhooks\Listeners\ChargeRefunded;
use Give\Tests\Feature\Gateways\Stripe\TestTraits\HasMockStripeAccounts;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Stripe\Charge;
use Stripe\Event;

class ChargeRefundedTest extends TestCase
{
    use RefreshDatabase, HasMockStripeAccounts;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldUpdateSubscriptionStatusToRefunded()
    {
        $this->addMockStripeAccounts();

        $donation = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'gatewayTransactionId' => 'stripe-payment-intent-id',
            'gatewayId' => NextGenStripeGateway::id()
        ]);

        $stripeCharge = Charge::constructFrom([
            'payment_intent' => $donation->gatewayTransactionId,
            'refunded' => true,
        ]);

        $mockEvent = Event::constructFrom([
            'data' => [
                'object' => $stripeCharge
            ]
        ]);

        $listener = new ChargeRefunded();

        $listener->processEvent($mockEvent);

         //refresh donation model
        $donation = Donation::find($donation->id);

        $this->assertTrue($donation->status->isRefunded());
    }
}