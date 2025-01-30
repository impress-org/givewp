<?php

namespace Unit\Donations\Routes;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationRoute;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @unreleased
 */
class GetDonationRouteTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @throws Exception
     */
    public function testGetDonation()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create();

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($donation->id, $data['id']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationShouldNotReturnSensitiveData()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create();

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveProperties = [
            'donorIp',
            'email',
            'phone',
            'billingAddress',
        ];

        $this->assertEquals(200, $status);
        $this->assertEmpty(array_intersect_key($data, $sensitiveProperties));
    }
}
