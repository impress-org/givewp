<?php

namespace Give\Tests\Unit\EventTickets\Routes;

use Exception;
use Give\Donations\Endpoints\ListDonations;
use Give\Donations\ListTable\DonationsListTable;
use Give\Donations\Models\Donation;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;
use Give\EventTickets\Routes\GetEventsListTable;
use Give\EventTickets\Routes\UpdateEvent;
use Give\EventTickets\Routes\UpdateEventTicketType;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class UpdateEventTicketTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    protected function getMockRequest($id): WP_REST_Request
    {
        $request = new WP_REST_Request(
            WP_REST_Server::EDITABLE,
            "/give-api/v2/event-tickets/ticket-type/$id"
        );

        $request->set_param('ticket_type_id', $id);

        return $request;
    }

    /**
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function testShouldUpdateTicketTypeTitle()
    {
        $ticketType = EventTicketType::factory()->create([
            'title' => 'Old Title',
        ]);

        $mockRequest = $this->getMockRequest($ticketType->id);
        $mockRequest->set_param('title', 'New Title');
        $response = (new UpdateEventTicketType())->handleRequest($mockRequest);

        $this->assertEquals('New Title', EventTicketType::find($ticketType->id)->title);
    }
}
