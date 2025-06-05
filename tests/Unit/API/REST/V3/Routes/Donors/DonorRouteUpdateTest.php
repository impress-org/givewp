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

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldPersistNewSchemaFields()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testUpdateDonorShouldPersistNewSchemaFields',
                'user_pass' => 'testUpdateDonorShouldPersistNewSchemaFields',
                'user_email' => 'testUpdateDonorShouldPersistNewSchemaFields@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request('PUT', $route);
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
    public function testUpdateDonorShouldValidateAdditionalEmailsFormat()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testUpdateDonorShouldValidateAdditionalEmailsFormat',
                'user_pass' => 'testUpdateDonorShouldValidateAdditionalEmailsFormat',
                'user_email' => 'testUpdateDonorShouldValidateAdditionalEmailsFormat@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request('PUT', $route);
        $request->set_body_params([
            'additionalEmails' => ['invalid-email', 'valid@example.com'],
        ]);

        $response = $this->dispatchRequest($request);

        // WordPress REST API validation would catch this
        // The exact status code may vary depending on how WordPress handles email validation
        $this->assertNotEquals(200, $response->get_status());
    }

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldValidatePhonePattern()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testUpdateDonorShouldValidatePhonePattern',
                'user_pass' => 'testUpdateDonorShouldValidatePhonePattern',
                'user_email' => 'testUpdateDonorShouldValidatePhonePattern@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        // Test valid phone numbers
        $validPhones = [
            '+1 (555) 123-4567',
            '555-123-4567',
            '15551234567',
            '+15551234567',
            '1 555 123 4567',
            null, // null should be allowed
            '', // empty string should be allowed
        ];

        foreach ($validPhones as $phone) {
            $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
            $request = new WP_REST_Request('PUT', $route);
            $request->set_body_params([
                'phone' => $phone,
            ]);

            $response = $this->dispatchRequest($request);
            $this->assertEquals(200, $response->get_status(), "Valid phone '{$phone}' should be accepted");
        }
    }

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldValidateAvatarIdPattern()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testUpdateDonorShouldValidateAvatarIdPattern',
                'user_pass' => 'testUpdateDonorShouldValidateAvatarIdPattern',
                'user_email' => 'testUpdateDonorShouldValidateAvatarIdPattern@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        // Test valid avatar IDs
        $validAvatarIds = [
            123,      // integer
            '456',    // string with numbers
            '0',      // zero as string
            0,        // zero as integer
            null,     // null should be allowed
            '',       // empty string should be allowed
        ];

        foreach ($validAvatarIds as $avatarId) {
            $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
            $request = new WP_REST_Request('PUT', $route);
            $request->set_body_params([
                'avatarId' => $avatarId,
            ]);

            $response = $this->dispatchRequest($request);
            $this->assertEquals(200, $response->get_status(), "Valid avatarId '{$avatarId}' should be accepted");
        }
    }

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldAcceptNullValuesForOptionalFields()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testUpdateDonorShouldAcceptNullValuesForOptionalFields',
                'user_pass' => 'testUpdateDonorShouldAcceptNullValuesForOptionalFields',
                'user_email' => 'testUpdateDonorShouldAcceptNullValuesForOptionalFields@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request('PUT', $route);
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

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldRejectInvalidPhonePatterns()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testUpdateDonorShouldRejectInvalidPhonePatterns',
                'user_pass' => 'testUpdateDonorShouldRejectInvalidPhonePatterns',
                'user_email' => 'testUpdateDonorShouldRejectInvalidPhonePatterns@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        // Test invalid phone numbers that should fail the regex pattern
        $invalidPhones = [
            '123',           // too short
            'abc-def-ghij',  // contains letters
            '123-456-78901', // too long for typical format
            '0123456789',    // starts with 0 (pattern requires 1-9)
        ];

        foreach ($invalidPhones as $phone) {
            $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
            $request = new WP_REST_Request('PUT', $route);
            $request->set_body_params([
                'phone' => $phone,
            ]);

            $response = $this->dispatchRequest($request);

            // The exact validation behavior may depend on WordPress REST API implementation
            // But invalid patterns should not result in a successful 200 response
            $this->assertNotEquals(200, $response->get_status(), "Invalid phone '{$phone}' should be rejected");
        }
    }

    /**
     * @unreleased
     */
    public function testUpdateDonorShouldRejectInvalidAvatarIdPatterns()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testUpdateDonorShouldRejectInvalidAvatarIdPatterns',
                'user_pass' => 'testUpdateDonorShouldRejectInvalidAvatarIdPatterns',
                'user_email' => 'testUpdateDonorShouldRejectInvalidAvatarIdPatterns@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        // Test invalid avatar IDs that should fail the numeric pattern
        $invalidAvatarIds = [
            'abc',      // contains letters
            '12a34',    // mixed letters and numbers
            'avatar123', // starts with letters
            '12.34',    // contains decimal
            '-123',     // negative number
        ];

        foreach ($invalidAvatarIds as $avatarId) {
            $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
            $request = new WP_REST_Request('PUT', $route);
            $request->set_body_params([
                'avatarId' => $avatarId,
            ]);

            $response = $this->dispatchRequest($request);

            // Invalid patterns should not result in a successful 200 response
            $this->assertNotEquals(200, $response->get_status(), "Invalid avatarId '{$avatarId}' should be rejected");
        }
    }
}
