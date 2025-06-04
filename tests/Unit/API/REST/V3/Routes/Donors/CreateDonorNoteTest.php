<?php

namespace Unit\API\REST\V3\Routes\Donors;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Donors\Models\Donor;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @unreleased
 */
class CreateDonorNoteTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testCreateDonorNote()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testCreateDonorNote',
                'user_pass' => 'testCreateDonorNote',
                'user_email' => 'testCreateDonorNote@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes';
        $request = new WP_REST_Request(WP_REST_Server::CREATABLE, $route);
        $request->set_body_params([
            'content' => 'New test note',
            'type' => 'admin',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(201, $status);
        $this->assertEquals($donor->id, $data['donorId']);
        $this->assertEquals('New test note', $data['content']);
        $this->assertEquals('admin', $data['type']);
    }

    /**
     * @unreleased
     */
    public function testCreateDonorNoteShouldReturn403ErrorWhenNotAdminUser()
    {
        $newSubscriberUser = $this->factory()->user->create(
            [
                'role' => 'subscriber',
                'user_login' => 'testCreateDonorNoteShouldReturn403Error',
                'user_pass' => 'testCreateDonorNoteShouldReturn403Error',
                'user_email' => 'testCreateDonorNoteShouldReturn403Error@test.com',
            ]
        );
        wp_set_current_user($newSubscriberUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes';
        $request = new WP_REST_Request(WP_REST_Server::CREATABLE, $route);
        $request->set_body_params([
            'content' => 'New test note',
            'type' => 'admin',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }
} 