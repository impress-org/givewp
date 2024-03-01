<?php

namespace Give\Tests\Unit\EventTickets\Routes;

use Exception;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Endpoints\ListDonations;
use Give\Donations\ListTable\DonationsListTable;
use Give\Donations\Models\Donation;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicket;
use Give\EventTickets\Routes\GetEventForms;
use Give\EventTickets\Routes\GetEventsListTable;
use Give\EventTickets\Routes\GetEventTickets;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class GetEventTicketsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    protected function getMockRequest(int $eventId): WP_REST_Request
    {
        $request = new WP_REST_Request(
            WP_REST_Server::READABLE,
            "/give-api/v2/events-tickets/event/$eventId/tickets"
        );
        $request->set_param('event_id', $eventId);

        return $request;
    }

    public function testTicketHasAttendeeName()
    {
        $ticket = EventTicket::factory()->create();

        $response = (new GetEventTickets)->handleRequest(
            $this->getMockRequest($ticket->event->id)
        );

        $this->assertEquals($ticket->donation->donor->name, $response->data[0]['attendee']);
    }

}
