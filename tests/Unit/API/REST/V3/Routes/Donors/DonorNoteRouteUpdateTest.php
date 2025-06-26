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
class DonorNoteRouteUpdateTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @since 4.4.0
     */
    public function testUpdateDonorNote()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testUpdateDonorNote',
                'user_pass' => 'testUpdateDonorNote',
                'user_email' => 'testUpdateDonorNote@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonorNote $note */
        $note = DonorNote::create([
            'donorId' => $donor->id,
            'content' => 'Original note content',
            'type' => DonorNoteType::ADMIN(),
        ]);

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes/' . $note->id;
        $request = new WP_REST_Request('PUT', $route);
        $request->set_body_params([
            'content' => 'Updated note content',
            'type' => 'admin',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['donorId']);
        $this->assertEquals('Updated note content', $data['content']);
        $this->assertEquals('admin', $data['type']);

        // Verify the note was actually updated in the database
        $updatedNote = DonorNote::find($note->id);
        $this->assertEquals('Updated note content', $updatedNote->content);
    }

    /**
     * @since 4.4.0
     */
    public function testUpdateDonorNoteShouldReturn403ErrorWhenNotAdminUser()
    {
        $newSubscriberUser = $this->factory()->user->create(
            [
                'role' => 'subscriber',
                'user_login' => 'testUpdateDonorNoteShouldReturn403Error',
                'user_pass' => 'testUpdateDonorNoteShouldReturn403Error',
                'user_email' => 'testUpdateDonorNoteShouldReturn403Error@test.com',
            ]
        );
        wp_set_current_user($newSubscriberUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonorNote $note */
        $note = DonorNote::create([
            'donorId' => $donor->id,
            'content' => 'Original note content',
            'type' => DonorNoteType::ADMIN(),
        ]);

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes/' . $note->id;
        $request = new WP_REST_Request('PUT', $route);
        $request->set_body_params([
            'content' => 'Updated note content',
            'type' => 'admin',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @since 4.4.0
     */
    public function testUpdateDonorNoteShouldReturn404ErrorWhenNoteNotFound()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testUpdateDonorNoteShouldReturn404Error',
                'user_pass' => 'testUpdateDonorNoteShouldReturn404Error',
                'user_email' => 'testUpdateDonorNoteShouldReturn404Error@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes/999';
        $request = new WP_REST_Request('PUT', $route);
        $request->set_body_params([
            'content' => 'Updated note content',
            'type' => 'admin',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(404, $status);
    }
}
