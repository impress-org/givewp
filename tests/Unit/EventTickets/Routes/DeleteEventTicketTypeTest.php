<?php

namespace Unit\EventTickets\Routes;

use Exception;
use Give\EventTickets\Models\EventTicket;
use Give\EventTickets\Models\EventTicketType;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Response;

class DeleteEventTicketTypeTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * Test that a valid request successfully deletes an Event Ticket Type.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testEventTicketTypeDeletionSuccess()
    {
        $ticketTypeId = EventTicketType::factory()->create()->id;

        $response = $this->handleRequest($ticketTypeId);

        $this->assertEquals(200, $response->get_status());
        $this->assertNull(EventTicketType::find($ticketTypeId));
    }

    /**
     * Test that an invalid ticket type ID returns an error.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testEventTicketTypeDeletionWithInvalidId()
    {
        $response = $this->handleRequest(PHP_INT_MAX);

        $this->assertErrorResponse('rest_invalid_param', $response);
    }

    /**
     * Test that a ticket type with associated tickets cannot be deleted.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testEventTicketTypeDeletionPreventedByTickets()
    {
        $eventTicket = EventTicket::factory()->create();
        $ticketTypeId = $eventTicket->ticketTypeId;

        $response = $this->handleRequest($ticketTypeId);

        $this->assertErrorResponse('rest_invalid_param', $response);

        $errorData = $response->as_error()->get_error_data();
        if (isset($errorData['params'])) {
            $this->assertContains(
                'ticket_type_id',
                array_keys($errorData['params'])
            );
            $this->assertEquals(
                'event_ticket_type_sold_delete_failed',
                $errorData['details']['ticket_type_id']['code']
            );
            $this->assertEquals(
                403,
                $errorData['details']['ticket_type_id']['data']['status']
            );
        }
    }

    /**
     * Test that unauthorized requests are denied.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testEventTicketTypeDeletionRequiresAuthorization()
    {
        $ticketTypeId = EventTicketType::factory()->create()->id;

        $response = $this->handleRequest($ticketTypeId, false);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * Handle the request common to all tests.
     *
     * @since 3.20.0
     *
     * @param int  $ticketTypeId
     * @param bool $authenticatedAsAdmin
     *
     * @return WP_REST_Response
     */
    private function handleRequest(
        int $ticketTypeId,
        bool $authenticatedAsAdmin = true
    ): WP_REST_Response {
        $request = $this->createRequest(
            'DELETE',
            "/give-api/v2/events-tickets/ticket-type/{$ticketTypeId}",
            [],
            $authenticatedAsAdmin ? 'administrator' : 'anonymous'
        );

        return $this->dispatchRequest($request);
    }
}
