<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Donations;

use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @unreleased
 */
class DonationStatisticsRouteGetTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testGetDonationStatistics()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonationStatistics',
                'user_pass' => 'testGetDonationStatistics',
                'user_email' => 'testGetDonationStatistics@test.com',
            ]
        );

        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'donorId' => $donor->id,
            'campaignId' => $campaign->id,
            'amount' => Money::fromDecimal(100, 'USD'),
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
        ]);

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . "/$donation->id/statistics";
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals([
            'donation' => [
                'amount' => $data['donation']['amount'],
                'feeAmountRecovered' => $data['donation']['feeAmountRecovered'],
                'status' => $data['donation']['status'],
                'date' => $data['donation']['date'],
                'paymentMethod' => $data['donation']['paymentMethod'],
                'mode' => $data['donation']['mode'],
            ],
            'donor' => [
                'id' => $donor->id,
                'name' => $donor->name,
                'email' => $donor->email,
            ],
            'campaign' => [
                'id' => $campaign->id,
                'title' => $campaign->title,
            ],
            'receipt' => [
                'donationDetails' => $data['receipt']['donationDetails'],
                'subscriptionDetails' => $data['receipt']['subscriptionDetails'],
                'eventTicketsDetails' => $data['receipt']['eventTicketsDetails'],
                'additionalDetails' => $data['receipt']['additionalDetails'],
            ]
        ], $data);
    }

    /**
     * @unreleased
     */
    public function testGetDonationStatisticsShouldReturn404ForNonExistentDonation()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonationStatistics404',
                'user_pass' => 'testGetDonationStatistics404',
                'user_email' => 'testGetDonationStatistics404@test.com',
            ]
        );

        wp_set_current_user($newAdminUser);

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/999/statistics';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(404, $status);
        $this->assertEquals('donation_not_found', $data['code']);
    }

    /**
     * @unreleased
     */
    public function testGetDonationStatisticsShouldReturn403ForNonAdminUser()
    {
        $newSubscriberUser = $this->factory()->user->create(
            [
                'role' => 'subscriber',
                'user_login' => 'testGetDonationStatistics403',
                'user_pass' => 'testGetDonationStatistics403',
                'user_email' => 'testGetDonationStatistics403@test.com',
            ]
        );

        wp_set_current_user($newSubscriberUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'donorId' => $donor->id,
            'campaignId' => $campaign->id,
        ]);

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . "/$donation->id/statistics";
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @unreleased
     */
    public function testGetDonationStatisticsShouldFilterByMode()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonationStatisticsMode',
                'user_pass' => 'testGetDonationStatisticsMode',
                'user_email' => 'testGetDonationStatisticsMode@test.com',
            ]
        );

        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'donorId' => $donor->id,
            'campaignId' => $campaign->id,
            'mode' => DonationMode::TEST(),
        ]);

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . "/$donation->id/statistics";
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params([
            'mode' => DonationMode::TEST,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(DonationMode::TEST, $data['donation']['mode']);
    }
}
