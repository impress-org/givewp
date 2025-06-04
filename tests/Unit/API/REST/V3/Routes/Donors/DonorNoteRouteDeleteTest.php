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
 * @unreleased
 */
class DonorNoteRouteDeleteTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testDeleteDonorNote()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testDeleteDonorNote',
                'user_pass' => 'testDeleteDonorNote',
                'user_email' => 'testDeleteDonorNote@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonorNote $note */
        $note = DonorNote::create([
            'donorId' => $donor->id,
            'content' => 'Test note to delete',
            'type' => DonorNoteType::ADMIN(),
        ]);

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes/' . $note->id;
        $request = new WP_REST_Request(WP_REST_Server::DELETABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(200, $status);
        $this->assertNull(DonorNote::find($note->id));
    }

    /**
     * @unreleased
     */
    public function testDeleteDonorNoteShouldReturn403ErrorWhenNotAdminUser()
    {
        $newSubscriberUser = $this->factory()->user->create(
            [
                'role' => 'subscriber',
                'user_login' => 'testDeleteDonorNoteShouldReturn403Error',
                'user_pass' => 'testDeleteDonorNoteShouldReturn403Error',
                'user_email' => 'testDeleteDonorNoteShouldReturn403Error@test.com',
            ]
        );
        wp_set_current_user($newSubscriberUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonorNote $note */
        $note = DonorNote::create([
            'donorId' => $donor->id,
            'content' => 'Test note to delete',
            'type' => DonorNoteType::ADMIN(),
        ]);

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes/' . $note->id;
        $request = new WP_REST_Request(WP_REST_Server::DELETABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }
} 