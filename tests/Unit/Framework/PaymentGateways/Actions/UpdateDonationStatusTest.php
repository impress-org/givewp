<?php

namespace Give\Tests\Framework\PaymentGateways\Actions;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Actions\UpdateDonationStatus;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class UpdateDonationStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @dataProvider donationStatus
     *
     * @throws Exception
     */
    public function testShouldUpdateStatus(string $constant, DonationStatus $status)
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayTransactionId' => 'gateway-transaction-id',
            'status' => DonationStatus::PENDING(),
        ]);

        give(UpdateDonationStatus::class)($donation, $status);

        $donation = Donation::find($donation->id); // Re-fetch

        $this->assertTrue($donation->status->equals($status));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testInvalidSubscriptionFirstPaymentShouldCancelSubscription()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $subscription->status = SubscriptionStatus::PENDING();
        $subscription->save();

        $donation->status = DonationStatus::PENDING();
        $donation->save();

        // Case 1 - Cancelled first payment
        give(UpdateDonationStatus::class)($donation, DonationStatus::CANCELLED());
        $subscription = Subscription::find($subscription->id); // Re-fetch
        $this->assertTrue($subscription->status->isCancelled());

        $donation->status = DonationStatus::PENDING();
        $donation->save();
        $donation = Donation::find($donation->id); // Re-fetch

        $subscription->status = SubscriptionStatus::PENDING();
        $subscription->save();
        $subscription = Subscription::find($subscription->id); // Re-fetch

        // Case 2 - Abandoned first payment
        give(UpdateDonationStatus::class)($donation, DonationStatus::ABANDONED());
        $subscription = Subscription::find($subscription->id); // Re-fetch
        $this->assertTrue($subscription->status->isCancelled());
    }

    /**
     * @unreleased
     */
    public function donationStatus(): array
    {
        $values = [];
        foreach (DonationStatus::values() as $key => $value) {
            $values[] = [$key, $value];
        }

        return $values;
    }
}
