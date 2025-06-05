<?php

namespace Unit\API\REST\V3\Routes\Donors;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Donors\Models\Donor;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;

class DonorRouteUpdateTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldUpdateModelProperties()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testUpdateDonorShouldUpdateModelProperties',
                'user_pass' => 'testUpdateDonorShouldUpdateModelProperties',
                'user_email' => 'testUpdateDonorShouldUpdateModelProperties@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request('PUT', $route);
        $request->set_body_params([
            'firstName' => 'Updated First Name',
            'lastName' => 'Updated Last Name',
            'email' => 'updated@test.com',
            'phone' => '1234567890',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals('Updated First Name', $data['firstName']);
        $this->assertEquals('Updated Last Name', $data['lastName']);
        $this->assertEquals('updated@test.com', $data['email']);
        $this->assertEquals('1234567890', $data['phone']);
    }

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldNotUpdateNonEditableFields()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testUpdateDonorShouldNotUpdateNonEditableFields',
                'user_pass' => 'testUpdateDonorShouldNotUpdateNonEditableFields',
                'user_email' => 'testUpdateDonorShouldNotUpdateNonEditableFields@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();
        $originalId = $donor->id;
        $originalUserId = $donor->userId;
        $originalCreatedAt = $donor->createdAt;

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request('PUT', $route);
        $request->set_body_params([
            //'id' => 999, // If you uncomment it, the ID from the path will be overridden and make the test return 404 error
            'userId' => 999,
            'createdAt' => '2024-01-01',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($originalId, $data['id']);
        $this->assertEquals($originalUserId, $data['userId']);
        $this->assertEquals($originalCreatedAt, $data['createdAt']);
    }

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldReturn404ErrorWhenDonorNotFound()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testUpdateDonorShouldReturn404ErrorWhenDonorNotFound',
                'user_pass' => 'testUpdateDonorShouldReturn404ErrorWhenDonorNotFound',
                'user_email' => 'testUpdateDonorShouldReturn404ErrorWhenDonorNotFound@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/999';
        $request = new WP_REST_Request('PUT', $route);
        $request->set_body_params([
            'firstName' => 'Updated First Name',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(404, $status);
    }

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldReturn403ErrorWhenNotAdminUser()
    {
        $newSubscriberUser = $this->factory()->user->create(
            [
                'role' => 'subscriber',
                'user_login' => 'testUpdateDonorShouldReturn403ErrorWhenNotAdminUser',
                'user_pass' => 'testUpdateDonorShouldReturn403ErrorWhenNotAdminUser',
                'user_email' => 'testUpdateDonorShouldReturn403ErrorWhenNotAdminUser@test.com',
            ]
        );
        wp_set_current_user($newSubscriberUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request('PUT', $route);
        $request->set_body_params([
            'firstName' => 'Updated First Name',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @unreleased
     */
    /*public function testUpdateDonorShouldValidateRequiredFields()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testUpdateDonorShouldValidateRequiredFields',
                'user_pass' => 'testUpdateDonorShouldValidateRequiredFields',
                'user_email' => 'testUpdateDonorShouldValidateRequiredFields@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request('PUT', $route);
        
        // Set content type to application/json
        $request->set_header('Content-Type', 'application/json');
        
        // Set body as JSON
        $request->set_body(json_encode([
            'firstName' => '',
            'lastName' => '',
            //'email' => '',
        ]));

        $response = rest_do_request($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(400, $status);
        $this->assertArrayHasKey('firstName', $data);
        $this->assertArrayHasKey('lastName', $data);
        //$this->assertArrayHasKey('email', $data);
    }*/
}
