<?php

namespace Unit\EventTickets\Routes;

use Exception;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Response;

class UpdateEventTicketTypeTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * Test that a valid request successfully updates an Event Ticket Type's title.
     *
     * @unreleased
     * @throws Exception
     */
    public function testEventTicketTypeTitleUpdateSuccess()
    {
        $ticketType = EventTicketType::factory()->create([
            'title' => 'Old Title',
        ]);

        $response = $this->handleRequest($ticketType->id, ['title' => 'New Title']);

        $this->assertEquals(200, $response->get_status());
        $this->assertEquals('New Title', EventTicketType::find($ticketType->id)->title);
    }

    /**
     * Test that a valid request successfully updates an Event Ticket Type's price.
     *
     * @unreleased
     * @throws Exception
     */
    public function testEventTicketTypePriceUpdateSuccess()
    {
        $ticketType = EventTicketType::factory()->create([
            'price' => new Money(1000, 'USD'),
        ]);

        $response = $this->handleRequest($ticketType->id, ['price' => 2000]);

        $this->assertEquals(200, $response->get_status());
        $this->assertEquals(2000, EventTicketType::find($ticketType->id)->price->formatToMinorAmount());
    }

    /**
     * Test that an invalid Event Ticket Type ID returns an error.
     *
     * @unreleased
     * @throws Exception
     */
    public function testEventTicketTypeUpdateWithInvalidId()
    {
        $response = $this->handleRequest(PHP_INT_MAX, ['title' => 'New Title']);

        $this->assertErrorResponse('rest_invalid_param', $response);
    }

    /**
     * Test that unauthorized requests are denied.
     *
     * @unreleased
     * @throws Exception
     */
    public function testEventUpdateRequiresAuthorization()
    {
        $ticketTypeId = EventTicketType::factory()->create()->id;

        $response = $this->handleRequest($ticketTypeId, ['title' => 'New Title'], false);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * Handle the request common to all tests.
     *
     * @unreleased
     *
     * @param int   $ticketTypeId
     * @param array $data
     * @param bool  $authenticatedAsAdmin
     *
     * @return WP_REST_Response
     */
    private function handleRequest(
        int $ticketTypeId,
        array $data,
        bool $authenticatedAsAdmin = true
    ): WP_REST_Response {
        $request = $this->createRequest(
            'POST',
            "/give-api/v2/events-tickets/ticket-type/{$ticketTypeId}",
            [],
            $authenticatedAsAdmin ? 'administrator' : 'anonymous'
        );

        $request->set_body_params($data);

        return $this->dispatchRequest($request);
    }
}
