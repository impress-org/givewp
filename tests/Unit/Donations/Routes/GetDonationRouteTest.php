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
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

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
    public function testGetDonationShouldNotIncludeSensitiveData()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

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
    public function testGetDonationShouldIncludeSensitiveData()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonationShouldIncludeSensitiveData',
                'user_pass' => 'testGetDonationShouldIncludeSensitiveData',
                'user_email' => 'testGetDonationShouldIncludeSensitiveData@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $request->set_query_params(
            [
                'includeSensitiveData' => true,
            ]
        );

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
    public function testGetDonationShouldReturn403ErrorWhenNotAdminUserIncludeSensitiveData()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $request->set_query_params(
            [
                'includeSensitiveData' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }


    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationShouldNotReturnAnonymousDonationByDefault()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);


        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationShouldIncludeAnonymousDonation()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonationsShouldIncludeAnonymousDonations',
                'user_pass' => 'testGetDonationsShouldIncludeAnonymousDonations',
                'user_email' => 'testGetDonationsShouldIncludeAnonymousDonations@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $request->set_query_params(
            [
                'anonymousDonations' => 'include',
            ]
        );

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
    public function testGetDonationShouldReturn403ErrorWhenNotAdminUserIncludeAnonymousDonation()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $request->set_query_params(
            [
                'anonymousDonations' => 'include',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationShouldRedactAnonymousDonation()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $request->set_query_params(
            [
                'anonymousDonations' => 'redact',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($donation->id, $data['id']);

        $anonymousDataRedacted = [
            'donorId',
            'honorific',
            'firstName',
            'lastName',
            'company',
        ];

        foreach ($anonymousDataRedacted as $property) {
            $this->assertEquals(__('anonymous', 'give'), $data[$property]);
        }
    }
}
