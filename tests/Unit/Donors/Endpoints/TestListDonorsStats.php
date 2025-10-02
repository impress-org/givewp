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
 * Test class for ListDonorsStats endpoint.
 * 
 * Note: Both DonationFactory and SubscriptionFactory automatically create donors when creating
 * donations and subscriptions respectively. This means we don't need to explicitly create donors
 * using Donor::factory() - the factories handle donor creation internally via their definition()
 * methods which include 'donorId' => Donor::factory()->create()->id.
 * 
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
        Donation::factory()->count(2)->create();

        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donors/stats');

        $endpoint = new ListDonorsStats();
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
        Subscription::factory()->count(3)->create();

        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donors/stats');

        $endpoint = new ListDonorsStats();
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
        Donation::factory()->count(2)->create();
        Subscription::factory()->count(2)->create();

        $request = new WP_REST_Request('GET', '/give-api/v2/admin/donors/stats');

        $endpoint = new ListDonorsStats();
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

        $endpoint = new ListDonorsStats();
        $response = $endpoint->handleRequest($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $data = $response->get_data();

        $this->assertEquals(0, $data['donorsCount']);
        $this->assertEquals(0, $data['oneTimeDonorsCount']);
        $this->assertEquals(0, $data['subscribersCount']);
    }
}
