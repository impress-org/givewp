<?php

namespace Give\Tests\Unit\Subscriptions\Endpoints;

use Give\Subscriptions\Endpoints\ListSubscriptionStats;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationMode;
use Give\Subscriptions\Models\Subscription;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Test class for ListSubscriptionStats endpoint.
 * 
 * @unreleased
 */
class TestSubscriptionStats extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testShouldReturnCorrectStatisticsForTotalActiveSubscriptions()
    {
        $activeSubscriptions = Subscription::factory()->count(2)->create([
            'status' => SubscriptionStatus::ACTIVE(),
        ]);

        Subscription::factory()->count(1)->create(['status' => SubscriptionStatus::FAILING()]);
        Subscription::factory()->count(1)->create(['status' => SubscriptionStatus::CANCELLED()]);
        Subscription::factory()->count(1)->create(['status' => SubscriptionStatus::REFUNDED()]);

        foreach ($activeSubscriptions as $subscription) {
            $donation = Donation::factory()->create([
                'amount' => new Money(5000, 'USD'),
                'status' => DonationStatus::COMPLETE(),
                'mode' => DonationMode::LIVE(),
            ]);
            give()->payment_meta->update_meta($donation->id, 'subscription_id', $subscription->id);
        }

        $request = new WP_REST_Request('GET', '/give-api/v2/admin/subscriptions/stats');
        $request->set_param('testMode', false);

        $endpoint = new ListSubscriptionStats();
        $response = $endpoint->handleRequest($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);

        $data = $response->get_data();

        $this->assertEquals(2, $data['activeSubscriptions']);
    }

    /**
     * @unreleased
     */
    public function testShouldReturnCorrectStatisticsForTotalContributionsOfSubscriptionDonationsOnly()
    {
        $subscription1 = Subscription::factory()->create([
            'status' => SubscriptionStatus::ACTIVE(),
        ]);
        
        $subscription2 = Subscription::factory()->create([
            'status' => SubscriptionStatus::ACTIVE(),
        ]);

        // Create donations with subscription IDs
        $donation1 = Donation::factory()->create([
            'amount' => new Money(5000, 'USD'), // $50.00
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
        ]);
        give()->payment_meta->update_meta($donation1->id, 'subscription_id', $subscription1->id);

        $donation2 = Donation::factory()->create([
            'amount' => new Money(7500, 'USD'), // $75.00
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
        ]);
        give()->payment_meta->update_meta($donation2->id, 'subscription_id', $subscription1->id);

        $donation3 = Donation::factory()->create([
            'amount' => new Money(10000, 'USD'), // $100.00
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
        ]);
        give()->payment_meta->update_meta($donation3->id, 'subscription_id', $subscription2->id);
        

        // Create donation without subscription ID (should NOT be counted)
        Donation::factory()->create([
            'amount' => new Money(2500, 'USD'), // $25.00
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
        ]);

        $request = new WP_REST_Request('GET', '/give-api/v2/admin/subscriptions/stats');
        $request->set_param('testMode', false);

        $endpoint = new ListSubscriptionStats();
        $response = $endpoint->handleRequest($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);

        $data = $response->get_data();

        // Total should be 5000 + 7500 + 10000 = 22500 cents = $225.00
        // (only subscription donations, excludes single donations with subscription_id = 0)
        $this->assertEquals(225.00, $data['totalContributions']);
        $this->assertEquals(2, $data['activeSubscriptions']);
    }
}
