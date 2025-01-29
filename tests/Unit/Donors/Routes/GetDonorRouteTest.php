<?php

namespace Unit\Donors\Routes;

use Exception;
use Give\Donations\ValueObjects\DonationRoute;
use Give\Donors\Models\Donor;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class GetDonorRouteTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @throws Exception
     */
    public function testGetDonor()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonationRoute::NAMESPACE . '/donors/' . $donor->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
    }
}
