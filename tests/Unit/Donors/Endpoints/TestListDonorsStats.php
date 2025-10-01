<?php

namespace Give\Tests\Unit\Donors\Endpoints;

use DateTime;
use Give\Donors\Endpoints\ListDonorsStats;
use Give\Donors\Models\Donor;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */
class TestListDonorsStats extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testShouldReturnCorrectStatisticsForMultipleOneTimeDonorsOnly()
    {
        // Create 2 donors
        $donors = Donor::factory()->count(2)->create();

        // Add One-time Donations to each donor
        foreach ($donors as $donor) {
            Donation::factory()->create([
                'donorId' => $donor->id,
                'status' => DonationStatus::COMPLETE()
            ]);
        }

        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donors/stats');

        $endpoint = new ListDonorStats();
        $response = $endpoint->handleRequest($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();

        $this->assertEquals(2, $data['donorsCount']);
        $this->assertEquals(2, $data['oneTimeDonorsCount']);
        $this->assertEquals(0, $data['subscribersCount']);
    }

    /**
     * @unreleased
     */
    public function testShouldReturnCorrectStatisticsForMultipleSubscriptionDonorsOnly()
    {
        // Create 3 donors
        $donors = Donor::factory()->count(3)->create();

        // Add subscriptions with donations to each donor
        foreach ($donors as $donor) {
            Subscription::factory()->createWithDonation([
                'donorId' => $donor->id,
                'status' => SubscriptionStatus::ACTIVE()
            ]);
        }

        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donors/stats');

        $endpoint = new ListDonorStats();
        $response = $endpoint->handleRequest($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();

        $this->assertEquals(3, $data['donorsCount']);
        $this->assertEquals(0, $data['oneTimeDonorsCount']);
        $this->assertEquals(3, $data['subscribersCount']);
    }

    /**
     * @unreleased
     */
    public function testShouldReturnCorrectStatisticsForMultipleMixedDonorTypes()
    {
        // Create 2 donors for one-time donations
        $oneTimeDonors = Donor::factory()->count(2)->create();

        // Add one-time donations to each donor
        foreach ($oneTimeDonors as $donor) {
            Donation::factory()->create([
                'donorId' => $donor->id,
                'status' => DonationStatus::COMPLETE()
            ]);
        }

        // Create 2 donors for recurring donations
        $recurringDonors = Donor::factory()->count(2)->create();
        foreach ($recurringDonors as $donor) {
            Subscription::factory()->createWithDonation([
                'donorId' => $donor->id,
                'status' => SubscriptionStatus::ACTIVE()
            ]);
        }

        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donors/stats');

        $endpoint = new ListDonorStats();
        $response = $endpoint->handleRequest($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();

        $this->assertEquals(4, $data['donorsCount']);
        $this->assertEquals(2, $data['oneTimeDonorsCount']);
        $this->assertEquals(2, $data['subscribersCount']);
    }

    /**
     * @unreleased
     */
    public function testShouldReturnEmptyStatisticsForNoDonors()
    {
        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donors/stats');

        $endpoint = new ListDonorStats();
        $response = $endpoint->handleRequest($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();

        $this->assertEquals(0, $data['donorsCount']);
        $this->assertEquals(0, $data['oneTimeDonorsCount']);
        $this->assertEquals(0, $data['subscribersCount']);
    }

    /**
     * @unreleased
     */
    private function createSubscription(int $campaignId, ?DateTime $donationDate = null): Subscription
    {
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::ACTIVE(),
            'mode' => SubscriptionMode::LIVE(),
        ]);

        // Update the donation to have the correct campaignId
        $donation = $subscription->initialDonation();
        $donation->campaignId = $campaignId;
        $donation->createdAt = $donationDate ?? new DateTime('now');
        $donation->save();

        return $subscription;
    }
}
