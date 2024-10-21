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
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class UpdateEventTicketTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.6.0
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
     * @since 3.6.0
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

    /**
     * @since 3.6.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldUpdateTicketTypePrice()
    {
        $ticketType = EventTicketType::factory()->create([
            'price' => new Money(1000, 'USD'),
        ]);

        $mockRequest = $this->getMockRequest($ticketType->id);
        $mockRequest->set_param('price', 2000);
        $response = (new UpdateEventTicketType())->handleRequest($mockRequest);

        $this->assertEquals(2000, EventTicketType::find($ticketType->id)->price->formatToMinorAmount());
    }
}
