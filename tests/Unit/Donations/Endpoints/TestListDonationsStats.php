<?php

namespace Give\Tests\Unit\Donations\Endpoints;

use DateTime;
use Give\Donations\Endpoints\ListDonationsStats;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Donations\ValueObjects\DonationMode;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Framework\Database\DB;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\ValueObjects\Money;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */
class TestListDonationsStats extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testShouldReturnCorrectStatisticsForMixedDonations()
    {
        // Create 2 one-time donations
        Donation::factory()->count(2)->create([
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
        ]);

        // Create 1 subscription with donation
        $this->createSubscription(1);

        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donations/stats');

        $endpoint = new ListDonationsStats();
        $response = $endpoint->handleRequest($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();

        $this->assertEquals(3, $data['donationsCount']);
        $this->assertEquals(2, $data['oneTimeDonationsCount']);
        $this->assertEquals(1, $data['recurringDonationsCount']);
    }

    /**
     * @unreleased
     */
    public function testShouldReturnCorrectStatisticsForOneTimeDonationsOnly()
    {
        // Create 3 one-time donations
        Donation::factory()->count(3)->create([
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
        ]);

        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donations/stats');

        $endpoint = new ListDonationsStats();
        $response = $endpoint->handleRequest($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();

        $this->assertEquals(3, $data['donationsCount']);
        $this->assertEquals(3, $data['oneTimeDonationsCount']);
        $this->assertEquals(0, $data['recurringDonationsCount']);
    }

    /**
     * @unreleased
     */
    public function testShouldReturnCorrectStatisticsForRecurringDonationsOnly()
    {
        // Create 2 subscriptions with donations
        $this->createSubscription(1);
        $this->createSubscription(1);

        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donations/stats');

        $endpoint = new ListDonationsStats();
        $response = $endpoint->handleRequest($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();

        $this->assertEquals(2, $data['donationsCount']);
        $this->assertEquals(0, $data['oneTimeDonationsCount']);
        $this->assertEquals(2, $data['recurringDonationsCount']);
    }

    /**
     * @unreleased
     */
    public function testShouldReturnEmptyStatisticsForNoDonations()
    {
        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donations/stats');

        $endpoint = new ListDonationsStats();
        $response = $endpoint->handleRequest($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();

        $this->assertEquals(0, $data['donationsCount']);
        $this->assertEquals(0, $data['oneTimeDonationsCount']);
        $this->assertEquals(0, $data['recurringDonationsCount']);
    }

    /**
     * @unreleased
     */
    public function testShouldFilterByTestMode()
    {
        // Create test donations
        Donation::factory()->count(2)->create([
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::TEST(),
        ]);

        // Create live donations
        Donation::factory()->count(3)->create([
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
        ]);

        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donations/stats');
        $request->set_param('testMode', true);

        $endpoint = new ListDonationsStats();
        $response = $endpoint->handleRequest($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();

        // Should only count test donations
        $this->assertEquals(2, $data['donationsCount']);
        $this->assertEquals(2, $data['oneTimeDonationsCount']);
        $this->assertEquals(0, $data['recurringDonationsCount']);
    }

    /**
     * @unreleased
     */
    public function testShouldExcludeTrashDonations()
    {
        // Create active donations
        Donation::factory()->count(2)->create([
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
        ]);

        // Create trash donations
        Donation::factory()->count(1)->create([
            'status' => DonationStatus::TRASH(),
            'mode' => DonationMode::LIVE(),
        ]);

        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donations/stats');

        $endpoint = new ListDonationsStats();
        $response = $endpoint->handleRequest($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();

        // Should only count active donations (exclude trash)
        $this->assertEquals(2, $data['donationsCount']);
        $this->assertEquals(2, $data['oneTimeDonationsCount']);
        $this->assertEquals(0, $data['recurringDonationsCount']);
    }

    /**
     * @unreleased
     */
    private function createSubscription(int $campaignId, DateTime $donationDate = null): Subscription
    {
        $subscription = Subscription::factory()->createWithDonation([
            'status' => \Give\Subscriptions\ValueObjects\SubscriptionStatus::ACTIVE(),
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
