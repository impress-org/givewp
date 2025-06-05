<?php

namespace Unit\API\REST\V3\Routes\Donors;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Donors\Models\Donor;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use WP_REST_Request;

class DonorRouteUpdateTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldUpdateModelProperties()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'firstName' => 'Updated First Name',
            'lastName' => 'Updated Last Name',
            'email' => 'updated@test.com',
            'phone' => '1234567890',
            'company' => 'Updated Company',
            'avatarId' => 123,
            'additionalEmails' => ['test1@example.com', 'test2@example.com'],
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals('Updated First Name', $data['firstName']);
        $this->assertEquals('Updated Last Name', $data['lastName']);
        $this->assertEquals('updated@test.com', $data['email']);
        $this->assertEquals('1234567890', $data['phone']);
        $this->assertEquals('Updated Company', $data['company']);
        $this->assertEquals(123, $data['avatarId']);
        $this->assertEquals(['test1@example.com', 'test2@example.com'], $data['additionalEmails']);
    }

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldNotUpdateNonEditableFields()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();
        $originalId = $donor->id;
        $originalUserId = $donor->userId;
        $originalCreatedAt = $donor->createdAt;

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
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
        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/999';
        $request = $this->createRequest('PUT', $route, [], 'administrator');
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
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest('PUT', $route, [], 'subscriber');
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

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldPersistNewSchemaFields()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'additionalEmails' => ['test1@example.com', 'test2@example.com'],
            'phone' => '+1 (555) 123-4567',
            'company' => 'Test Company LLC',
            'avatarId' => '123',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(['test1@example.com', 'test2@example.com'], $data['additionalEmails']);
        $this->assertEquals('+1 (555) 123-4567', $data['phone']);
        $this->assertEquals('Test Company LLC', $data['company']);
        $this->assertEquals('123', $data['avatarId']);

        // Verify persistence in database
        $updatedDonor = Donor::find($donor->id);
        $this->assertEquals(['test1@example.com', 'test2@example.com'], $updatedDonor->additionalEmails);
        $this->assertEquals('+1 (555) 123-4567', $updatedDonor->phone);
        $this->assertEquals('Test Company LLC', $updatedDonor->company);
        $this->assertEquals('123', $updatedDonor->avatarId);
    }

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldPersistPhoneNumbers()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        // Test phone number persistence
        $testPhones = [
            '+1 (555) 123-4567',
            '555-123-4567',
            '15551234567',
            '+15551234567',
            '1 555 123 4567',
        ];

        foreach ($testPhones as $phone) {
            $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
            $request = $this->createRequest('PUT', $route, [], 'administrator');
            $request->set_body_params([
                'phone' => $phone,
            ]);

            $response = $this->dispatchRequest($request);
            $data = $response->get_data();

            $this->assertEquals(200, $response->get_status());
            $this->assertEquals($phone, $data['phone']);

            // Verify persistence in database
            $updatedDonor = Donor::find($donor->id);
            $this->assertEquals($phone, $updatedDonor->phone);
        }
    }

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldPersistAvatarId()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        // Test avatar ID persistence
        $testAvatarIds = [
            123,      // integer
            '456',    // string with numbers
            '0',      // zero as string
            0,        // zero as integer
        ];

        foreach ($testAvatarIds as $avatarId) {
            $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
            $request = $this->createRequest('PUT', $route, [], 'administrator');
            $request->set_body_params([
                'avatarId' => $avatarId,
            ]);

            $response = $this->dispatchRequest($request);
            $data = $response->get_data();

            $this->assertEquals(200, $response->get_status());
            $this->assertEquals($avatarId, $data['avatarId']);

            // Verify persistence in database
            $updatedDonor = Donor::find($donor->id);
            $this->assertEquals($avatarId, $updatedDonor->avatarId);
        }
    }

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldAcceptNullValuesForOptionalFields()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'phone' => null,
            'company' => null,
            'avatarId' => null,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertNull($data['phone']);
        $this->assertNull($data['company']);
        $this->assertNull($data['avatarId']);
    }
}
