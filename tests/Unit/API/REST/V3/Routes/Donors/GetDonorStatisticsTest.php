<?php

namespace Unit\API\REST\V3\Routes\Donors;

use Exception;
use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\API\REST\V3\Routes\Donors\RegisterDonorRoutes;
use Give\Donors\Models\Donor;
use Give\Helpers\Hooks;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @unreleased
 */
class GetDonorStatisticsTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function setUp(): void
    {
        Hooks::addAction('rest_api_init', RegisterDonorRoutes::class);

        parent::setUp();
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonorStatisticsShouldReturnAllStatics()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorStatisticsShouldReturnAllStatics',
                'user_pass' => 'testGetDonorStatisticsShouldReturnAllStatics',
                'user_email' => 'testGetDonorStatisticsShouldReturnAllStatics@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonationRoute::NAMESPACE . "/donors/$donor->id/statistics";
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertEquals([
            'lifetimeDonations' => 300,
            'highestDonation' => 250,
            'averageDonation' => 150,
        ], $data);
    }
}
