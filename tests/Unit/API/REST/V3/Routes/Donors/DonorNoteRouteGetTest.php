<?php

namespace Unit\API\REST\V3\Routes\Donors;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Donors\Models\Donor;
use Give\Donors\Models\DonorNote;
use Give\Donors\ValueObjects\DonorNoteType;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @since 4.4.0
 */
class DonorNoteRouteGetTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @since 4.4.0
     */
    public function testGetDonorNote()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorNote',
                'user_pass' => 'testGetDonorNote',
                'user_email' => 'testGetDonorNote@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonorNote $note */
        $note = DonorNote::create([
            'donorId' => $donor->id,
            'content' => 'Test note content',
            'type' => DonorNoteType::ADMIN(),
        ]);

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes/' . $note->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($note->id, $data['id']);
        $this->assertEquals($note->donorId, $data['donorId']);
        $this->assertEquals($note->content, $data['content']);
        $this->assertEquals($note->type->getValue(), $data['type']);
        $this->assertArrayHasKey('createdAt', $data);
    }

    /**
     * @since 4.4.0
     */
    public function testGetDonorNoteShouldReturn404ErrorWhenNoteNotFound()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorNoteShouldReturn404Error',
                'user_pass' => 'testGetDonorNoteShouldReturn404Error',
                'user_email' => 'testGetDonorNoteShouldReturn404Error@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes/999';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(404, $status);
    }
}
