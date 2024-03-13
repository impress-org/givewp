<?php

namespace Give\Tests\Unit\EventTickets\Routes;

use Exception;
use Give\Donations\Endpoints\ListDonations;
use Give\Donations\ListTable\DonationsListTable;
use Give\Donations\Models\Donation;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Routes\GetEventsListTable;
use Give\EventTickets\Routes\UpdateEvent;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class UpdateEventTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.6.0
     */
    protected function getMockRequest($eventId): WP_REST_Request
    {
        $request = new WP_REST_Request(
            WP_REST_Server::EDITABLE,
            "/give-api/v2/event-tickets/event/$eventId"
        );

        $request->set_param('event_id', $eventId);

        return $request;
    }

    /**
     * @since 3.6.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldUpdateEventTitle()
    {
        $event = Event::factory()->create([
            'title' => 'Old Title',
        ]);

        $mockRequest = $this->getMockRequest($event->id);
        $mockRequest->set_param('title', 'New Title');
        $response = (new UpdateEvent())->handleRequest($mockRequest);

        $this->assertEquals('New Title', Event::find($event->id)->title);
    }
}
