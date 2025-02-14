<?php

namespace Unit\Donations\Routes;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationRoute;
use Give\Donations\ValueObjects\DonationStatus;
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
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE()]);

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
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE()]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveData = [
            'donorIp',
            'email',
            'phone',
            'billingAddress',
        ];

        $this->assertEquals(200, $status);
        $this->assertEmpty(array_intersect_key($data, array_flip($sensitiveData)));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationShouldReturnSensitiveData()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'admin38974238473824',
                'user_pass' => 'admin38974238473824',
                'user_email' => 'admin38974238473824@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE()]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveData = [
            'donorIp',
            'email',
            'phone',
            'billingAddress',
        ];

        $this->assertEquals(200, $status);
        $this->assertNotEmpty(array_intersect_key($data, array_flip($sensitiveData)));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationShouldNotReturnSensitiveAndAnonymousData()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $request->set_query_params(
            [
                'includeAnonymousDonations' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveAndAnonymousData = [
            // sensitive data
            'donorIp',
            'email',
            'phone',
            'billingAddress',
            // anonymous data
            'donorId',
            'honorific',
            'firstName',
            'lastName',
            'company',
        ];

        $this->assertEquals(200, $status);
        $this->assertEmpty(array_intersect_key($data, array_flip($sensitiveAndAnonymousData)));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationShouldReturnSensitiveAndAnonymousData()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'admin38974238473824',
                'user_pass' => 'admin38974238473824',
                'user_email' => 'admin38974238473824@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $request->set_query_params(
            [
                'includeAnonymousDonations' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveAndAnonymousData = [
            // sensitive data
            'donorIp',
            'email',
            'phone',
            'billingAddress',
            // anonymous data
            'donorId',
            'honorific',
            'firstName',
            'lastName',
            'company',
        ];

        $this->assertEquals(200, $status);
        $this->assertNotEmpty(array_intersect_key($data, array_flip($sensitiveAndAnonymousData)));
    }
}
