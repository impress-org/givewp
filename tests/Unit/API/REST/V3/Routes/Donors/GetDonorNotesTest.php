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
class GetDonorNotesTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testGetDonorNotesShouldReturnAllModelProperties()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorNotesShouldReturnAllModelProperties',
                'user_pass' => 'testGetDonorNotesShouldReturnAllModelProperties',
                'user_email' => 'testGetDonorNotesShouldReturnAllModelProperties@test.com',
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

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        // Remove additional property add by the prepare_response_for_collection() method
        unset($data[0]['_links']);

        $createdAtJson = json_encode($data[0]['createdAt']);

        $this->assertEquals(200, $status);
        $this->assertEquals([
            'id' => $note->id,
            'donorId' => $note->donorId,
            'content' => $note->content,
            'type' => $note->type->getValue(),
            'createdAt' => json_decode($createdAtJson, true),
        ], $data[0]);
    }

    /**
     * @unreleased
     */
    public function testGetDonorNotesShouldReturnSelfLink()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorNotesShouldReturnSelfLink',
                'user_pass' => 'testGetDonorNotesShouldReturnSelfLink',
                'user_email' => 'testGetDonorNotesShouldReturnSelfLink@test.com',
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

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $this->responseToData($response, true);

        $this->assertEquals(200, $status);
        $this->assertEquals($note->id, $data[0]['id']);
        $this->assertArrayHasKey('_links', $data[0]);
        $this->assertArrayHasKey('self', $data[0]['_links']);
    }

    /**
     * @unreleased
     */
    public function testGetDonorNotesWithPagination()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorNotesWithPagination',
                'user_pass' => 'testGetDonorNotesWithPagination',
                'user_email' => 'testGetDonorNotesWithPagination@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        
        DonorNote::create([
            'donorId' => $donor->id,
            'content' => 'Test note 1',
            'type' => DonorNoteType::ADMIN(),
        ]);

        
        DonorNote::create([
            'donorId' => $donor->id,
            'content' => 'Test note 2',
            'type' => DonorNoteType::ADMIN(),
        ]);

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $request->set_query_params([
            'page' => 1,
            'per_page' => 1,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $headers = $response->get_headers();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals(2, $headers['X-WP-Total']);
        $this->assertEquals(2, $headers['X-WP-TotalPages']);
    }

    /**
     * @unreleased
     */
    public function testGetDonorNotesShouldReturn403ErrorWhenNotAdminUser()
    {
        $newSubscriberUser = $this->factory()->user->create(
            [
                'role' => 'subscriber',
                'user_login' => 'testGetDonorNotesShouldReturn403Error',
                'user_pass' => 'testGetDonorNotesShouldReturn403Error',
                'user_email' => 'testGetDonorNotesShouldReturn403Error@test.com',
            ]
        );
        wp_set_current_user($newSubscriberUser);

        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id . '/notes';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }
} 