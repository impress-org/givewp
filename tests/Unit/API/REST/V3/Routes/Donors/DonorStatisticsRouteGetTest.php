<?php

namespace Unit\API\REST\V3\Routes\Donors;

use Exception;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;
use DateTime;

/**
 * @since 4.4.0
 */
class DonorStatisticsRouteGetTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @since 4.4.0
     *
     * @throws Exception
     */
    public function testGetDonorStatistics()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorStatistics',
                'user_pass' => 'testGetDonorStatistics',
                'user_email' => 'testGetDonorStatistics@test.com',
            ]
        );

        wp_set_current_user($newAdminUser);

        /** @var  Donor $donor */
        $donor = Donor::factory()->create();
        $this->createDonationUsd250Amount($donor->id);
        $this->createDonationUsd50Amount($donor->id);

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . "/$donor->id/statistics";
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertEquals([
            'donations' => [
                'lifetimeAmount' => 300,
                'highestAmount' => 250,
                'averageAmount' => 150,
                'first' => [
                    'amount' => '250',
                    'date' => $data['donations']['first']['date']
                ],
                'count' => 2,
                'last' => $data['donations']['last']
            ],
            'donorType' => $data['donorType'],
            'preferredPaymentMethod' => $data['preferredPaymentMethod']
        ], $data);
    }

    /**
     * @since 4.4.0
     *
     * @throws Exception
     */
    public function testGetDonorStatisticsShouldFilterByCampaign()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorStatisticsShouldFilterByCampaign',
                'user_pass' => 'testGetDonorStatisticsShouldFilterByCampaign',
                'user_email' => 'testGetDonorStatisticsShouldFilterByCampaign@test.com',
            ]
        );

        wp_set_current_user($newAdminUser);

        /** @var Campaign $campaignUsd250Amount */
        $campaignUsd250Amount = Campaign::factory()->create();

        /** @var Campaign $campaignUsd50Amount */
        $campaignUsd50Amount = Campaign::factory()->create();

        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $this->createDonationUsd250Amount($donor->id, $campaignUsd250Amount->id);
        $this->createDonationUsd50Amount($donor->id, $campaignUsd50Amount->id);


        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . "/$donor->id/statistics";
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'campaignId' => $campaignUsd50Amount->id,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertEquals([
            'donations' => [
                'lifetimeAmount' => 50,
                'highestAmount' => 50,
                'averageAmount' => 50,
                'first' => [
                    'amount' => '50',
                    'date' => $data['donations']['first']['date']
                ],
                'count' => 1,
                'last' => $data['donations']['last']
            ],
            'donorType' => $data['donorType'],
            'preferredPaymentMethod' => $data['preferredPaymentMethod']
        ], $data);
    }

    /**
     * @since 4.4.0
     *
     * @throws Exception
     */
    public function testGetDonorStatisticsShouldFilterByMode()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorStatisticsShouldFilterByMode',
                'user_pass' => 'testGetDonorStatisticsShouldFilterByMode',
                'user_email' => 'testGetDonorStatisticsShouldFilterByMode@test.com',
            ]
        );

        wp_set_current_user($newAdminUser);

        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $this->createDonationUsd250Amount($donor->id);
        $donationUsd50Amount = $this->createDonationUsd50Amount($donor->id);

        $donationUsd50Amount->mode = DonationMode::TEST();
        $donationUsd50Amount->save();


        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . "/$donor->id/statistics";
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'mode' => DonationMode::TEST,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertEquals([
            'donations' => [
                'lifetimeAmount' => 50,
                'highestAmount' => 50,
                'averageAmount' => 50,
                'first' => [
                    'amount' => '50',
                    'date' => $data['donations']['first']['date']
                ],
                'count' => 1,
                'last' => $data['donations']['last']
            ],
            'donorType' => $data['donorType'],
            'preferredPaymentMethod' => $data['preferredPaymentMethod']
        ], $data);
    }

    /**
     * @since 4.4.0
     *
     * @throws Exception
     */
    private function createDonationUsd250Amount(int $donorId, int $campaignId = 0): Donation
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create([
            'donorId' => $donorId,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(25000, 'USD'),
            'mode' => DonationMode::LIVE(),
            'createdAt' => new DateTime('2025-06-04 23:12:26'),
        ]);

        if ($campaignId) {
            give()->payment_meta->update_meta($donation->id, DonationMetaKeys::CAMPAIGN_ID, $campaignId);
        }

        return $donation;
    }

    /**
     * @since 4.4.0
     *
     * @throws Exception
     */
    private function createDonationUsd50Amount(int $donorId, int $campaignId = 0): Donation
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create([
            'donorId' => $donorId,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(5000, 'USD'),
            'mode' => DonationMode::LIVE(),
            'createdAt' => new DateTime('2025-06-05 23:12:26'),
        ]);

        if ($campaignId) {
            give()->payment_meta->update_meta($donation->id, DonationMetaKeys::CAMPAIGN_ID, $campaignId);
        }

        return $donation;
    }
}
