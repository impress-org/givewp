<?php

namespace Unit\API\REST\V3\Routes\Donors;

use Exception;
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

        /** @var DonorNote $note1 */
        $note1 = DonorNote::create([
            'donorId' => $donor->id,
            'content' => 'Test note 1',
            'type' => DonorNoteType::ADMIN(),
        ]);

        /** @var DonorNote $note2 */
        $note2 = DonorNote::create([
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
        //$this->assertEquals($note2->id, $data[0]['id']); // Most recent first
        $this->assertEquals(2, $headers['X-WP-Total']);
        $this->assertEquals(2, $headers['X-WP-TotalPages']);

        /*$request->set_query_params([
            'page' => 2,
            'per_page' => 1,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $headers = $response->get_headers();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($note1->id, $data[0]['id']);
        $this->assertEquals(2, $headers['X-WP-Total']);
        $this->assertEquals(2, $headers['X-WP-TotalPages']);*/
    }

    /**
     * @unreleased
     */
    public function testGetSingleDonorNote()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetSingleDonorNote',
                'user_pass' => 'testGetSingleDonorNote',
                'user_email' => 'testGetSingleDonorNote@test.com',
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
        $this->assertEquals($note->content, $data['content']);
        $this->assertEquals($note->type->getValue(), $data['type']);
    }

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

        $this->assertEquals(204, $status);
        $this->assertNull(DonorNote::find($note->id));
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

    /**
     * @unreleased
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

    /**
     * @unreleased
     */
    /*public function testGetDonorNoteShouldReturn404ErrorWhenDonorNotFound()
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

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/999/notes';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(404, $status);
    }*/
} 