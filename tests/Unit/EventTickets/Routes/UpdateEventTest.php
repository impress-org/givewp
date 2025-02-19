<?php

namespace Unit\EventTickets\Routes;

use Exception;
use Give\EventTickets\Models\Event;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Response;

class UpdateEventTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * Test that a valid request successfully updates an Event.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testEventUpdateSuccess()
    {
        $event = Event::factory()->create([
            'title' => 'Old Title',
        ]);

        $response = $this->handleRequest($event->id, ['title' => 'New Title']);

        $this->assertEquals(200, $response->get_status());
        $this->assertEquals('New Title', Event::find($event->id)->title);
    }

    /**
     * Test that an invalid Event ID returns an error.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testEventUpdateWithInvalidId()
    {
        $response = $this->handleRequest(PHP_INT_MAX, ['title' => 'New Title']);

        $this->assertErrorResponse('rest_invalid_param', $response);
    }

    /**
     * Test that unauthorized requests are denied.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testEventUpdateRequiresAuthorization()
    {
        $eventId = Event::factory()->create()->id;

        $response = $this->handleRequest($eventId, ['title' => 'New Title'], false);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * Handle the request common to all tests.
     *
     * @since 3.20.0
     *
     * @param int   $eventId
     * @param array $data
     * @param bool  $authenticatedAsAdmin
     *
     * @return WP_REST_Response
     */
    private function handleRequest(
        int $eventId,
        array $data,
        bool $authenticatedAsAdmin = true
    ): WP_REST_Response {
        $request = $this->createRequest(
            'POST',
            "/give-api/v2/events-tickets/event/{$eventId}",
            [],
            $authenticatedAsAdmin ? 'administrator' : 'anonymous'
        );

        $request->set_body_params($data);

        return $this->dispatchRequest($request);
    }
}
