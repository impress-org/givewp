<?php

namespace Unit\EventTickets\Routes;

use Exception;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Response;

class CreateEventTicketTypeTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * Test that a valid request successfully creates a new Event Ticket Type.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testEventTicketTypeCreationSuccess()
    {
        $eventId = Event::factory()->create()->id;
        $data = [
            'title' => 'New Title',
            'description' => 'New Description',
            'price' => 1000,
            'capacity' => 100,
        ];

        $response = $this->handleRequest($eventId, $data);
        $ticketType = EventTicketType::find($response->get_data()->id);

        $this->assertEquals(201, $response->get_status());
        $this->assertEquals(1, $ticketType->eventId);
        $this->assertEquals($eventId, $ticketType->eventId);
        $this->assertEquals($data['title'], $ticketType->title);
        $this->assertEquals($data['description'], $ticketType->description);
        $this->assertEquals(new Money($data['price'], give_get_currency()), $ticketType->price);
        $this->assertEquals($data['capacity'], $ticketType->capacity);
    }

    /**
     * Test that creating an Event Ticket Type giving an invalid event ID returns an error.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testEventTicketTypeCreationWithInvalidEventId()
    {
        $data = [
            'title' => 'New Title',
            'description' => 'New Description',
            'price' => 1000,
            'capacity' => 100,
        ];

        $response = $this->handleRequest(PHP_INT_MAX, $data);

        $this->assertErrorResponse('rest_invalid_param', $response);
    }

    /**
     * Test that creating an Event Ticket Type with missing required fields returns an error.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testEventTicketTypeCreationWithMissingRequiredFields()
    {
        $eventId = Event::factory()->create()->id;
        $response = $this->handleRequest($eventId, []);

        $this->assertErrorResponse('rest_missing_callback_param', $response);

        $errorData = $response->as_error()->get_error_data();
        if (isset($errorData['params'])) {
            $this->assertContains('title', $errorData['params']);
            $this->assertContains('price', $errorData['params']);
            $this->assertContains('capacity', $errorData['params']);
        }
    }

    /**
     * Test that unauthorized requests are denied.
     *
     * @since 3.20.0
     * @throws Exception
     */
    public function testEventTicketTypeCreationRequiresAuthorization()
    {
        $eventId = Event::factory()->create()->id;
        $data = [
            'title' => 'New Title',
            'price' => '1000',
            'capacity' => 100,
        ];

        $response = $this->handleRequest($eventId, $data, false);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * Handle the request common to all tests.
     *
     * @since 3.20.0
     *
     * @param int $eventId
     * @param array $data
     * @param bool $authenticatedAsAdmin
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
            "/give-api/v2/events-tickets/event/{$eventId}/ticket-types",
            [],
            $authenticatedAsAdmin ? 'administrator' : 'anonymous'
        );

        $request->set_body_params($data);

        return $this->dispatchRequest($request);
    }
}
