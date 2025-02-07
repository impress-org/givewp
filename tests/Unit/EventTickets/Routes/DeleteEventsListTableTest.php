<?php

namespace Unit\EventTickets\Routes;

use Exception;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicket;
use Give\EventTickets\Repositories\EventRepository;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Response;

class DeleteEventsListTableTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * Test that a valid request successfully deletes multiple events.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testMultipleEventsDeletionSuccess()
    {
        $events = Event::factory()->count(3)->create();
        $eventIds = array_map(function ($event) {
            return $event->id;
        }, $events);

        $response = $this->handleRequest($eventIds);

        $this->assertEquals(200, $response->get_status());
        $this->assertEquals(0, give(EventRepository::class)->prepareQuery()->whereIn('id', $eventIds)->count());
    }

    /**
     * Test that an invalid Event ID returns it in the errors array.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testEventDeletionWithInvalidId()
    {
        $response = $this->handleRequest([PHP_INT_MAX]);

        $this->assertCount(1, $response->get_data()['errors']);
    }

    /**
     * Test that an event with associated tickets cannot be deleted.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testEventDeletionPreventedByTickets()
    {
        $eventTicket = EventTicket::factory()->create();
        $eventId = $eventTicket->eventId;

        $response = $this->handleRequest([$eventId]);

        $this->assertCount(1, $response->get_data()['errors']);
    }

    /**
     * Test that unauthorized requests are denied.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testEventDeletionRequiresAuthorization()
    {
        $eventId = Event::factory()->create()->id;

        $response = $this->handleRequest([$eventId], false);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * Handle the request common to all tests.
     *
     * @since 3.20.0
     *
     * @param int[] $eventIds
     * @param bool  $authenticatedAsAdmin
     *
     * @return WP_REST_Response
     */
    private function handleRequest(
        array $eventIds,
        bool $authenticatedAsAdmin = true
    ): WP_REST_Response {
        $request = $this->createRequest(
            'DELETE',
            "/give-api/v2/events-tickets/events/list-table",
            [],
            $authenticatedAsAdmin ? 'administrator' : 'anonymous'
        );

        $request->set_param('ids', implode(',', $eventIds));

        return $this->dispatchRequest($request);
    }
}
